<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\Priority;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Enum\UserRole;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('client', EntityType::class, [
                'label' => 'Client',
                'class' => Client::class,
                'choice_label' => 'displayName',
                'query_builder' => static fn (EntityRepository $repo): \Doctrine\ORM\QueryBuilder => $repo->createQueryBuilder('c')->orderBy('c.displayName', 'ASC'),
            ])
            ->add('status', EnumType::class, [
                'label' => 'Statut',
                'class' => ProjectStatus::class,
                'choice_label' => static fn (ProjectStatus $s): string => $s->label(),
            ])
            ->add('currentStage', EnumType::class, [
                'label' => 'Étape actuelle',
                'class' => ProjectStage::class,
                'choice_label' => static fn (ProjectStage $s): string => $s->label(),
            ])
            ->add('priority', EnumType::class, [
                'label' => 'Priorité',
                'class' => Priority::class,
                'choice_label' => static fn (Priority $p): string => $p->label(),
            ])
            ->add('targetDeliveryDate', DateType::class, [
                'label' => 'Date de livraison cible',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('budgetTarget', MoneyType::class, [
                'label' => 'Budget cible',
                'currency' => 'EUR',
                'divisor' => 100,
            ])
            ->add('sellingPrice', MoneyType::class, [
                'label' => 'Prix de vente',
                'currency' => 'EUR',
                'divisor' => 100,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Brief / description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('assignedDesigner', EntityType::class, [
                'label' => 'Designer',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => '— Non assigné —',
                'query_builder' => static fn (EntityRepository $repo): \Doctrine\ORM\QueryBuilder => self::usersByRole($repo, UserRole::DESIGNER),
            ])
            ->add('assignedJeweler', EntityType::class, [
                'label' => 'Joaillier·ère',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => '— Non assigné —',
                'query_builder' => static fn (EntityRepository $repo): \Doctrine\ORM\QueryBuilder => self::usersByRole($repo, UserRole::JEWELER),
            ])
            ->add('assignedSetter', EntityType::class, [
                'label' => 'Sertisseur',
                'class' => User::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => '— Non assigné —',
                'query_builder' => static fn (EntityRepository $repo): \Doctrine\ORM\QueryBuilder => self::usersByRole($repo, UserRole::SETTER),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Project::class]);
    }

    private static function usersByRole(EntityRepository $repo, UserRole $role): \Doctrine\ORM\QueryBuilder
    {
        return $repo->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"'.$role->value.'"%')
            ->orderBy('u.firstName', 'ASC');
    }
}
