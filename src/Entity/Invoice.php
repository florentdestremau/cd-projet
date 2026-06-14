<?php

namespace App\Entity;

use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(name: 'invoices')]
#[ORM\UniqueConstraint(name: 'invoices_reference_uniq', columns: ['reference'])]
class Invoice
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

    #[ORM\ManyToOne(targetEntity: Quote::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Quote $quote = null;

    #[ORM\Column(length: 20, enumType: InvoiceStatus::class)]
    private InvoiceStatus $status = InvoiceStatus::DRAFT;

    /** @var Collection<int, InvoiceItem> */
    #[ORM\OneToMany(targetEntity: InvoiceItem::class, mappedBy: 'invoice', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column]
    private int $vatRate = 2000;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    /** @var Collection<int, Payment> */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private Collection $payments;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $r): self
    {
        $this->reference = $r;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $p): self
    {
        $this->project = $p;

        return $this;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $q): self
    {
        $this->quote = $q;

        return $this;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $s): self
    {
        $this->status = $s;

        return $this;
    }

    public function getVatRate(): int
    {
        return $this->vatRate;
    }

    public function setVatRate(int $r): self
    {
        $this->vatRate = $r;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $d): self
    {
        $this->dueDate = $d;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $d): self
    {
        $this->sentAt = $d;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $d): self
    {
        $this->paidAt = $d;

        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $p): self
    {
        $this->pdfPath = $p;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, InvoiceItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $i): self
    {
        if (!$this->items->contains($i)) {
            $this->items->add($i);
            $i->setInvoice($this);
        }

        return $this;
    }

    /** @return Collection<int, Payment> */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $p): self
    {
        if (!$this->payments->contains($p)) {
            $this->payments->add($p);
            $p->setInvoice($this);
        }

        return $this;
    }

    public function getTotalHt(): int
    {
        return array_sum(array_map(static fn (InvoiceItem $i): int => $i->getTotalHt(), $this->items->toArray()));
    }

    public function getTotalTtc(): int
    {
        return (int) round($this->getTotalHt() * (1 + $this->vatRate / 10000));
    }

    public function getAmountPaid(): int
    {
        return array_sum(array_map(static fn (Payment $p): int => $p->getAmount(), $this->payments->toArray()));
    }

    public function getBalanceDue(): int
    {
        return max(0, $this->getTotalTtc() - $this->getAmountPaid());
    }
}
