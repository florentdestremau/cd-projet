<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function get(string $key, string $default = ''): string
    {
        $s = $this->find($key);
        return $s?->getValue() ?? $default;
    }

    /** @param array<string, string|null> $values */
    public function setAll(array $values): void
    {
        $em = $this->getEntityManager();
        foreach ($values as $key => $value) {
            $s = $this->find($key) ?? new Setting($key);
            $s->setValue($value);
            $em->persist($s);
        }
        $em->flush();
    }
}
