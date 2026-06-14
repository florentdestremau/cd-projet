<?php

namespace App\Repository;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function generateNextReference(int $year): string
    {
        $prefix = \sprintf('BAG-%d-', $year);
        $last = $this->createQueryBuilder('p')
            ->where('p.reference LIKE :prefix')
            ->setParameter('prefix', $prefix.'%')
            ->orderBy('p.reference', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $next = 1;
        if ($last instanceof Project) {
            $suffix = (int) substr($last->getReference(), \strlen($prefix));
            $next = $suffix + 1;
        }

        return \sprintf('%s%03d', $prefix, $next);
    }

    /** @return list<Project> */
    public function findActiveOrdered(int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', ProjectStatus::ACTIVE)
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
