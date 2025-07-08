<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('phone', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Nouveau mot de passe',
                'constraints' => [
                    new Length(['min' => 8]),
                    new Regex([
                        'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'
                    ])
                ],
            ])
            ->add('confirmation', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Confirmation du mot de passe',
            ])
            ->add('site', null, [
                'choice_label' => 'name',
                'placeholder' => 'Sélectionner un site'
            ])
            ->add('picture', FileType::class, [
                'label' => 'Importer une nouvelle photo',
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
