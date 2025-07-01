<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingFilterTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Tous les sites',
                'label' => 'Site'
            ])
            ->add('search', TextType::class, [
                'label' => 'Nom contient',
                'required' => false,
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Entre le',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'et le',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l’organisateur',
                'required' => false,
            ])
            ->add('subscribed', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit',
                'required' => false,
            ])
            ->add('notSubscribed', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit',
                'required' => false,
            ])
            ->add('past', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET', // important pour les filtres
            'csrf_protection' => false
        ]);
    }
}
