<?php

namespace App\Entity;

use App\Enum\PaymentMethod;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payments')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Invoice $invoice = null;

    #[ORM\Column]
    private int $amount = 0;

    #[ORM\Column(length: 20, enumType: PaymentMethod::class)]
    private PaymentMethod $method = PaymentMethod::TRANSFER;

    #[ORM\Column]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;

    public function __construct()
    {
        $this->receivedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $i): self
    {
        $this->invoice = $i;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $a): self
    {
        $this->amount = $a;

        return $this;
    }

    public function getMethod(): PaymentMethod
    {
        return $this->method;
    }

    public function setMethod(PaymentMethod $m): self
    {
        $this->method = $m;

        return $this;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeImmutable $d): self
    {
        $this->receivedAt = $d;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $r): self
    {
        $this->reference = $r;

        return $this;
    }
}
