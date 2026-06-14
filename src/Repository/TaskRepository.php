<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /** @return list<Task> */
    public function findOpenForUser(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignee = :user')
            ->andWhere('t.completedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('t.dueDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
