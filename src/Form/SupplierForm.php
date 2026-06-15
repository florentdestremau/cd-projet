<?php

namespace App\Form;

use App\Entity\Supplier;
use App\Enum\SupplierSpecialty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SupplierForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('specialty', EnumType::class, [
                'label' => 'Spécialité',
                'class' => SupplierSpecialty::class,
                'choice_label' => static fn (SupplierSpecialty $s): string => $s->label(),
            ])
            ->add('contactEmail', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('contactPhone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Supplier::class]);
    }
}
