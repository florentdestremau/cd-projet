<?php

namespace App\Entity;

use App\Enum\MaterialType;
use App\Repository\MaterialRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterialRepository::class)]
#[ORM\Table(name: 'materials')]
class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 20, enumType: MaterialType::class)]
    private MaterialType $type = MaterialType::GOLD_18K;

    /** Prix par gramme en centimes */
    #[ORM\Column]
    private int $pricePerGram = 0;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Supplier $supplier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $n): self
    {
        $this->name = $n;

        return $this;
    }

    public function getType(): MaterialType
    {
        return $this->type;
    }

    public function setType(MaterialType $t): self
    {
        $this->type = $t;

        return $this;
    }

    public function getPricePerGram(): int
    {
        return $this->pricePerGram;
    }

    public function setPricePerGram(int $p): self
    {
        $this->pricePerGram = $p;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $s): self
    {
        $this->supplier = $s;

        return $this;
    }
}
