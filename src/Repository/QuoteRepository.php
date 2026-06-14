<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function generateNextReference(int $year): string
    {
        $prefix = sprintf('DEV-%d-', $year);
        $last = $this->createQueryBuilder('q')
            ->where('q.reference LIKE :p')
            ->setParameter('p', $prefix.'%')
            ->orderBy('q.reference', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        $next = 1;
        if ($last instanceof Quote) {
            $next = (int) substr($last->getReference(), strlen($prefix)) + 1;
        }
        return sprintf('%s%03d', $prefix, $next);
    }
}
