<?php

namespace App\Entity;

use App\Enum\ExpenseCategory;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ORM\Table(name: 'expenses')]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\Column(length: 20, enumType: ExpenseCategory::class)]
    private ExpenseCategory $category = ExpenseCategory::MATERIAL;

    #[ORM\Column]
    private int $amountHt = 0;

    #[ORM\Column]
    private int $vatAmount = 0;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $occurredAt;

    #[ORM\Column(length: 255)]
    private string $description = '';

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $supplierName = null;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ExpenseCategory
    {
        return $this->category;
    }

    public function setCategory(ExpenseCategory $c): self
    {
        $this->category = $c;

        return $this;
    }

    public function getAmountHt(): int
    {
        return $this->amountHt;
    }

    public function setAmountHt(int $a): self
    {
        $this->amountHt = $a;

        return $this;
    }

    public function getVatAmount(): int
    {
        return $this->vatAmount;
    }

    public function setVatAmount(int $v): self
    {
        $this->vatAmount = $v;

        return $this;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(\DateTimeImmutable $d): self
    {
        $this->occurredAt = $d;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $d): self
    {
        $this->description = $d;

        return $this;
    }

    public function getSupplierName(): ?string
    {
        return $this->supplierName;
    }

    public function setSupplierName(?string $n): self
    {
        $this->supplierName = $n;

        return $this;
    }
}
