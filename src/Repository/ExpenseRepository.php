<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function totalForProject(Project $project): int
    {
        $r = $this->createQueryBuilder('e')
            ->select('COALESCE(SUM(e.amountHt), 0) AS total')
            ->where('e.project = :p')->setParameter('p', $project)
            ->getQuery()->getSingleResult();
        return (int) $r['total'];
    }
}
