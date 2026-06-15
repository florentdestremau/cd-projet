<?php

namespace App\Form;

use App\Entity\Expense;
use App\Enum\ExpenseCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ExpenseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, ['label' => 'Description', 'empty_data' => ''])
            ->add('supplierName', TextType::class, ['label' => 'Fournisseur', 'required' => false])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => ExpenseCategory::class,
                'choice_label' => static fn (ExpenseCategory $c): string => $c->label(),
            ])
            ->add('amountHt', MoneyType::class, ['label' => 'Montant HT', 'currency' => 'EUR', 'divisor' => 100])
            ->add('vatAmount', MoneyType::class, ['label' => 'TVA', 'currency' => 'EUR', 'divisor' => 100, 'required' => false, 'empty_data' => '0'])
            ->add('occurredAt', DateType::class, ['label' => 'Date', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Expense::class]);
    }
}
