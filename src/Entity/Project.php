<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Priority;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
#[ORM\UniqueConstraint(name: 'projects_reference_uniq', columns: ['reference'])]
#[ORM\Index(name: 'projects_status_stage_idx', columns: ['status', 'current_stage'])]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $reference = '';

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Client $client = null;

    #[ORM\Column(length: 20, enumType: ProjectStatus::class)]
    private ProjectStatus $status = ProjectStatus::ACTIVE;

    #[ORM\Column(length: 30, enumType: ProjectStage::class)]
    private ProjectStage $currentStage = ProjectStage::BRIEF;

    #[ORM\Column(length: 10, enumType: Priority::class)]
    private Priority $priority = Priority::NORMAL;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $targetDeliveryDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\Column]
    private int $budgetTarget = 0;

    #[ORM\Column]
    private int $sellingPrice = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assignedDesigner = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assignedJeweler = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assignedSetter = null;

    /** @var Collection<int, ProjectStageStatus> */
    #[ORM\OneToMany(targetEntity: ProjectStageStatus::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stageStatuses;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->stageStatuses = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): self { $this->reference = $reference; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): self { $this->client = $client; return $this; }
    public function getStatus(): ProjectStatus { return $this->status; }
    public function setStatus(ProjectStatus $status): self { $this->status = $status; return $this; }
    public function getCurrentStage(): ProjectStage { return $this->currentStage; }
    public function setCurrentStage(ProjectStage $stage): self { $this->currentStage = $stage; return $this; }
    public function getPriority(): Priority { return $this->priority; }
    public function setPriority(Priority $priority): self { $this->priority = $priority; return $this; }
    public function getTargetDeliveryDate(): ?\DateTimeImmutable { return $this->targetDeliveryDate; }
    public function setTargetDeliveryDate(?\DateTimeImmutable $date): self { $this->targetDeliveryDate = $date; return $this; }
    public function getDeliveredAt(): ?\DateTimeImmutable { return $this->deliveredAt; }
    public function setDeliveredAt(?\DateTimeImmutable $date): self { $this->deliveredAt = $date; return $this; }
    public function getBudgetTarget(): int { return $this->budgetTarget; }
    public function setBudgetTarget(int $cents): self { $this->budgetTarget = $cents; return $this; }
    public function getSellingPrice(): int { return $this->sellingPrice; }
    public function setSellingPrice(int $cents): self { $this->sellingPrice = $cents; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getAssignedDesigner(): ?User { return $this->assignedDesigner; }
    public function setAssignedDesigner(?User $user): self { $this->assignedDesigner = $user; return $this; }
    public function getAssignedJeweler(): ?User { return $this->assignedJeweler; }
    public function setAssignedJeweler(?User $user): self { $this->assignedJeweler = $user; return $this; }
    public function getAssignedSetter(): ?User { return $this->assignedSetter; }
    public function setAssignedSetter(?User $user): self { $this->assignedSetter = $user; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function touch(): self { $this->updatedAt = new \DateTimeImmutable(); return $this; }

    /** @return Collection<int, ProjectStageStatus> */
    public function getStageStatuses(): Collection { return $this->stageStatuses; }

    public function addStageStatus(ProjectStageStatus $status): self
    {
        if (!$this->stageStatuses->contains($status)) {
            $this->stageStatuses->add($status);
            $status->setProject($this);
        }

        return $this;
    }

    public function progressPercent(): int
    {
        $applicable = $this->stageStatuses->filter(fn (ProjectStageStatus $s) => $s->isApplicable());
        $total = $applicable->count();
        if ($total === 0) {
            return 0;
        }
        $done = $applicable->filter(fn (ProjectStageStatus $s) => $s->getCompletedAt() !== null)->count();

        return (int) round($done / $total * 100);
    }
}
