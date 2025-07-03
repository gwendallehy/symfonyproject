<?php

// src/Form/PlaceType.php
namespace App\Form;

use App\Entity\Place;
use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du lieu',
            ])
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'attr' => [
                    'id' => 'address-input',
                    'placeholder' => 'Tapez une rue' // optionnel, utile pour l’autocomplétion
                ],
            ])

            ->add('cityName', TextType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'attr' => ['id' => 'place_cityName', 'autocomplete' => 'off']
            ])
            ->add('cityId', HiddenType::class, [
                'mapped' => false,
                'attr' => ['id' => 'place_cityId']
            ])


            ->add('latitude', TextType::class, [
                'attr' => ['id' => 'latitude-input', 'readonly' => true],
            ])
            ->add('longitude', TextType::class, [
                'attr' => ['id' => 'longitude-input', 'readonly' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}

