<?php

namespace App\Repository;

use App\Entity\Outgoing;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Outgoing>
 */
class OutgoingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outgoing::class);
    }
    public function findFilteredOutings(?User $user, ?array $filters): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.site', 's')
            ->leftJoin('o.participants', 'p')
            ->leftJoin('o.etat', 'e')
            ->addSelect('s', 'p', 'e')
            ->orderBy('o.dateBegin', 'DESC')
            ->andWhere('e.libelle != :archived')
            ->setParameter('archived', 'Archivée');

        if (!empty($filters['site'])) {
            $qb->andWhere('o.site = :site')
                ->setParameter('site', $filters['site']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(o.name) LIKE :search')
                ->setParameter('search', '%' . strtolower($filters['search']) . '%');
        }

        if (!empty($filters['dateStart'])) {
            $qb->andWhere('o.dateBegin >= :start')
                ->setParameter('start', $filters['dateStart']);
        }

        if (!empty($filters['dateEnd'])) {
            $qb->andWhere('o.dateBegin <= :end')
                ->setParameter('end', $filters['dateEnd']);
        }

        if (!empty($filters['organizer']) && $user) {
            $qb->andWhere('o.organizer = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['subscribed']) && $user) {
            $qb->andWhere(':user MEMBER OF o.participants')
                ->setParameter('user', $user);
        }

        if (!empty($filters['notSubscribed']) && $user) {
            $qb->andWhere(':user NOT MEMBER OF o.participants')
                ->setParameter('user', $user);
        }

        if (!empty($filters['past'])) {
            $qb->andWhere('o.dateBegin < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Outgoing[] Returns an array of Outgoing objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Outgoing
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
