<?php

namespace App\Form;

use App\Dto\CompanySettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SettingsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, ['label' => 'Nom de la maison'])
            ->add('companyTagline', TextType::class, ['label' => 'Baseline', 'required' => false])
            ->add('companyAddress', TextareaType::class, ['label' => 'Adresse', 'required' => false])
            ->add('companyEmail', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('companyPhone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('companyLegal', TextareaType::class, ['label' => 'Mentions légales', 'required' => false])
            ->add('defaultVatRate', NumberType::class, ['label' => 'TVA par défaut (%)', 'scale' => 2])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CompanySettings::class]);
    }
}
