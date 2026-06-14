<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserPushSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPushSubscriptionRepository::class)]
#[ORM\Table(name: 'user_push_subscriptions')]
#[ORM\UniqueConstraint(name: 'ups_endpoint_uniq', columns: ['endpoint'])]
class UserPushSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'text')]
    private string $endpoint = '';

    #[ORM\Column(length: 255)]
    private string $p256dhKey = '';

    #[ORM\Column(length: 255)]
    private string $authToken = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getEndpoint(): string { return $this->endpoint; }
    public function setEndpoint(string $endpoint): self { $this->endpoint = $endpoint; return $this; }
    public function getP256dhKey(): string { return $this->p256dhKey; }
    public function setP256dhKey(string $key): self { $this->p256dhKey = $key; return $this; }
    public function getAuthToken(): string { return $this->authToken; }
    public function setAuthToken(string $token): self { $this->authToken = $token; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
