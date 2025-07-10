<?php
namespace App\Form;
// src/Form/GroupType.php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('members', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'choice_label' => 'email', // ou 'username' selon ta config
            ]);
    }
}
