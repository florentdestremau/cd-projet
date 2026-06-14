<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_items')]
class InvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private string $description = '';

    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\Column]
    private int $unitPriceHt = 0;

    public function getId(): ?int { return $this->id; }
    public function getInvoice(): ?Invoice { return $this->invoice; }
    public function setInvoice(?Invoice $i): self { $this->invoice = $i; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $d): self { $this->description = $d; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $q): self { $this->quantity = $q; return $this; }
    public function getUnitPriceHt(): int { return $this->unitPriceHt; }
    public function setUnitPriceHt(int $p): self { $this->unitPriceHt = $p; return $this; }
    public function getTotalHt(): int { return $this->quantity * $this->unitPriceHt; }
}
