<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaskForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Tâche',
                'empty_data' => '',
                'attr' => ['placeholder' => 'Ex. Commander or 18k jaune'],
            ])
            ->add('assignee', EntityType::class, [
                'label' => 'Assigné',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => '— Personne en particulier —',
                'query_builder' => static fn (UserRepository $repo): \Doctrine\ORM\QueryBuilder => $repo->createQueryBuilder('u')->orderBy('u.firstName', 'ASC'),
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Échéance',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Task::class]);
    }
}
