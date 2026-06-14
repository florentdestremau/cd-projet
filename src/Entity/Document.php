<?php
declare(strict_types=1);
namespace App\Entity;

use App\Enum\DocumentCategory;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'documents')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private string $filename = '';

    #[ORM\Column(length: 255)]
    private string $storagePath = '';

    #[ORM\Column(length: 120)]
    private string $mimeType = '';

    #[ORM\Column]
    private int $size = 0;

    #[ORM\Column(length: 20, enumType: DocumentCategory::class)]
    private DocumentCategory $category = DocumentCategory::OTHER;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $uploadedBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $uploadedAt;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $p): self { $this->project = $p; return $this; }
    public function getFilename(): string { return $this->filename; }
    public function setFilename(string $n): self { $this->filename = $n; return $this; }
    public function getStoragePath(): string { return $this->storagePath; }
    public function setStoragePath(string $p): self { $this->storagePath = $p; return $this; }
    public function getMimeType(): string { return $this->mimeType; }
    public function setMimeType(string $m): self { $this->mimeType = $m; return $this; }
    public function getSize(): int { return $this->size; }
    public function setSize(int $s): self { $this->size = $s; return $this; }
    public function getCategory(): DocumentCategory { return $this->category; }
    public function setCategory(DocumentCategory $c): self { $this->category = $c; return $this; }
    public function getUploadedBy(): ?User { return $this->uploadedBy; }
    public function setUploadedBy(?User $u): self { $this->uploadedBy = $u; return $this; }
    public function getUploadedAt(): \DateTimeImmutable { return $this->uploadedAt; }

    public function isImage(): bool { return str_starts_with($this->mimeType, 'image/'); }
}
