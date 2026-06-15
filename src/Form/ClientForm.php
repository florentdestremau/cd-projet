<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ClientForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, ['label' => 'Nom', 'empty_data' => ''])
            ->add('companyName', TextType::class, ['label' => 'Société', 'required' => false])
            ->add('contactEmail', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('contactPhone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('address', TextareaType::class, ['label' => 'Adresse', 'required' => false])
            ->add('notes', TextareaType::class, ['label' => 'Notes confidentielles', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Client::class]);
    }
}
