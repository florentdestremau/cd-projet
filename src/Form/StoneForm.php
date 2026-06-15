<?php

namespace App\Form;

use App\Entity\Stone;
use App\Entity\Supplier;
use App\Enum\StoneType as StoneTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StoneForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'label' => 'Type',
                'class' => StoneTypeEnum::class,
                'choice_label' => static fn (StoneTypeEnum $t): string => $t->label(),
            ])
            ->add('caratWeight', IntegerType::class, [
                'label' => 'Poids (millièmes de carat, 1000 = 1.000 ct)',
                'attr' => ['min' => 1],
            ])
            ->add('quality', TextType::class, ['label' => 'Qualité', 'required' => false])
            ->add('color', TextType::class, ['label' => 'Couleur', 'required' => false])
            ->add('certificateRef', TextType::class, ['label' => 'Référence certificat', 'required' => false])
            ->add('costPrice', MoneyType::class, ['label' => 'Coût', 'currency' => 'EUR', 'divisor' => 100])
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
        $resolver->setDefaults(['data_class' => Stone::class]);
    }
}
