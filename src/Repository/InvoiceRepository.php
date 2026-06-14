<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function generateNextReference(int $year): string
    {
        $prefix = sprintf('FAC-%d-', $year);
        $last = $this->createQueryBuilder('i')
            ->where('i.reference LIKE :p')->setParameter('p', $prefix.'%')
            ->orderBy('i.reference', 'DESC')->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        $next = 1;
        if ($last instanceof Invoice) {
            $next = (int) substr($last->getReference(), strlen($prefix)) + 1;
        }
        return sprintf('%s%03d', $prefix, $next);
    }

    /** @return list<Invoice> */
    public function findOverdue(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status IN (:open)')->setParameter('open', [InvoiceStatus::SENT, InvoiceStatus::OVERDUE])
            ->andWhere('i.dueDate < :today')->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('i.dueDate', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return list<Invoice> */
    public function findPaidInMonth(\DateTimeImmutable $month): array
    {
        $start = $month->modify('first day of this month')->setTime(0, 0);
        $end = $month->modify('first day of next month')->setTime(0, 0);
        return $this->createQueryBuilder('i')
            ->where('i.paidAt >= :start AND i.paidAt < :end')
            ->setParameter('start', $start)->setParameter('end', $end)
            ->getQuery()->getResult();
    }
}
