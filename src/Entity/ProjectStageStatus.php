<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ProjectStage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'project_stage_statuses')]
#[ORM\UniqueConstraint(name: 'pss_project_stage_uniq', columns: ['project_id', 'stage'])]
class ProjectStageStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'stageStatuses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\Column(length: 30, enumType: ProjectStage::class)]
    private ProjectStage $stage;

    #[ORM\Column]
    private bool $applicable = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct(ProjectStage $stage)
    {
        $this->stage = $stage;
    }

    public function getId(): ?int { return $this->id; }
    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $project): self { $this->project = $project; return $this; }
    public function getStage(): ProjectStage { return $this->stage; }
    public function isApplicable(): bool { return $this->applicable; }
    public function setApplicable(bool $applicable): self { $this->applicable = $applicable; return $this; }
    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $date): self { $this->startedAt = $date; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $date): self { $this->completedAt = $date; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }
}
