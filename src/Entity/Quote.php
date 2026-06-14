<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\QuoteStatus;
use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
#[ORM\Table(name: 'quotes')]
#[ORM\UniqueConstraint(name: 'quotes_reference_uniq', columns: ['reference'])]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $reference = '';

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\Column(length: 20, enumType: QuoteStatus::class)]
    private QuoteStatus $status = QuoteStatus::DRAFT;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    /** @var Collection<int, QuoteItem> */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'quote', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    /** TVA en points × 100 (2000 = 20%) */
    #[ORM\Column]
    private int $vatRate = 2000;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): self { $this->reference = $reference; return $this; }
    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $project): self { $this->project = $project; return $this; }
    public function getStatus(): QuoteStatus { return $this->status; }
    public function setStatus(QuoteStatus $status): self { $this->status = $status; return $this; }
    public function getValidUntil(): ?\DateTimeImmutable { return $this->validUntil; }
    public function setValidUntil(?\DateTimeImmutable $d): self { $this->validUntil = $d; return $this; }
    public function getVatRate(): int { return $this->vatRate; }
    public function setVatRate(int $rate): self { $this->vatRate = $rate; return $this; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function setSentAt(?\DateTimeImmutable $d): self { $this->sentAt = $d; return $this; }
    public function getAcceptedAt(): ?\DateTimeImmutable { return $this->acceptedAt; }
    public function setAcceptedAt(?\DateTimeImmutable $d): self { $this->acceptedAt = $d; return $this; }
    public function getPdfPath(): ?string { return $this->pdfPath; }
    public function setPdfPath(?string $path): self { $this->pdfPath = $path; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, QuoteItem> */
    public function getItems(): Collection { return $this->items; }
    public function addItem(QuoteItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setQuote($this);
        }
        return $this;
    }

    public function getTotalHt(): int
    {
        return array_sum(array_map(fn (QuoteItem $i) => $i->getTotalHt(), $this->items->toArray()));
    }

    public function getTotalTtc(): int
    {
        return (int) round($this->getTotalHt() * (1 + $this->vatRate / 10000));
    }
}
