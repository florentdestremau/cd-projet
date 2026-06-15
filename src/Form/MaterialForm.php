<?php

namespace App\Form;

use App\Entity\Material;
use App\Entity\Supplier;
use App\Enum\MaterialType as MaterialTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MaterialForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('type', EnumType::class, [
                'label' => 'Type',
                'class' => MaterialTypeEnum::class,
                'choice_label' => static fn (MaterialTypeEnum $t): string => $t->label(),
            ])
            ->add('pricePerGram', MoneyType::class, ['label' => 'Prix au gramme', 'currency' => 'EUR', 'divisor' => 100])
            ->add('supplier', EntityType::class, [
                'label' => 'Fournisseur',
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '— Aucun —',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Material::class]);
    }
}
