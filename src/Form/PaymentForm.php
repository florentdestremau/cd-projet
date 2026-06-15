<?php

namespace App\Form;

use App\Entity\Payment;
use App\Enum\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, ['label' => 'Montant', 'currency' => 'EUR', 'divisor' => 100])
            ->add('method', EnumType::class, [
                'label' => 'Méthode',
                'class' => PaymentMethod::class,
                'choice_label' => static fn (PaymentMethod $m): string => $m->label(),
            ])
            ->add('reference', TextType::class, ['label' => 'Référence (optionnel)', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Payment::class]);
    }
}
