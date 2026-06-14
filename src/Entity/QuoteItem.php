<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'quote_items')]
class QuoteItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Quote $quote = null;

    #[ORM\Column(length: 255)]
    private string $description = '';

    #[ORM\Column]
    private int $quantity = 1;

    /** Prix unitaire HT en centimes */
    #[ORM\Column]
    private int $unitPriceHt = 0;

    public function getId(): ?int { return $this->id; }
    public function getQuote(): ?Quote { return $this->quote; }
    public function setQuote(?Quote $q): self { $this->quote = $q; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $d): self { $this->description = $d; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $q): self { $this->quantity = $q; return $this; }
    public function getUnitPriceHt(): int { return $this->unitPriceHt; }
    public function setUnitPriceHt(int $p): self { $this->unitPriceHt = $p; return $this; }
    public function getTotalHt(): int { return $this->quantity * $this->unitPriceHt; }
}
