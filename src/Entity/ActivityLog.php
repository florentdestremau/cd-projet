<?php

namespace App\Entity;

use App\Repository\ActivityLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: 'activity_logs')]
#[ORM\Index(name: 'activity_logs_created_idx', columns: ['created_at'])]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $actor = null;

    #[ORM\Column(length: 60)]
    private string $eventType = '';

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $user): self
    {
        $this->actor = $user;

        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $type): self
    {
        $this->eventType = $type;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @param array<string, mixed> $payload */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
