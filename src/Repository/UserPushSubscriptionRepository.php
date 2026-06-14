<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPushSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPushSubscription>
 */
class UserPushSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPushSubscription::class);
    }

    /** @return list<UserPushSubscription> */
    public function findForUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findByEndpoint(string $endpoint): ?UserPushSubscription
    {
        return $this->findOneBy(['endpoint' => $endpoint]);
    }
}
