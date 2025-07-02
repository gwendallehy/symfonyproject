<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

//Valide juste que le fichier est bien un CSV,
// sans se soucier du contenu ou du séparateur.

class CsvImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csvFile', FileType::class, [
            'label' => 'CSV File',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'maxSize' => '4M',
                    'mimeTypes' => [
                        'text/csv',
                        'application/csv',
                        'text/plain',
                    ],
                    'mimeTypesMessage' => 'Veuillez uploader un fichier CSV',
                ])
            ]
        ]);
    }
}
