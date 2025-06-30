<?php

namespace App\Form;

use App\Entity\Place;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('dateBegin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de début'
            ])
            ->add('duration')
            ->add('dateSubscriptionLimit', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date limite d’inscription'
            ])
            ->add('nbSubscriptionMax', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
