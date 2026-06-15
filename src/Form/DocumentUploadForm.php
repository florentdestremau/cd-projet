<?php

namespace App\Form;

use App\Dto\DocumentUpload;
use App\Enum\DocumentCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

final class DocumentUploadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'mapped' => true,
                'constraints' => [
                    new NotNull(message: 'Choisissez un fichier.'),
                    new File(maxSize: '10M'),
                ],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => DocumentCategory::class,
                'choice_label' => static fn (DocumentCategory $c): string => $c->label(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DocumentUpload::class]);
    }
}
