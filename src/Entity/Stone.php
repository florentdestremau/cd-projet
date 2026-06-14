<?php
declare(strict_types=1);
namespace App\Entity;

use App\Enum\StoneType;
use App\Repository\StoneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoneRepository::class)]
#[ORM\Table(name: 'stones')]
class Stone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, enumType: StoneType::class)]
    private StoneType $type = StoneType::DIAMOND;

    /** Poids en millièmes de carat (1000 = 1.000 ct) */
    #[ORM\Column]
    private int $caratWeight = 0;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $quality = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $certificateRef = null;

    /** Prix d'achat en centimes */
    #[ORM\Column]
    private int $costPrice = 0;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Supplier $supplier = null;

    public function getId(): ?int { return $this->id; }
    public function getType(): StoneType { return $this->type; }
    public function setType(StoneType $t): self { $this->type = $t; return $this; }
    public function getCaratWeight(): int { return $this->caratWeight; }
    public function setCaratWeight(int $w): self { $this->caratWeight = $w; return $this; }
    public function getCarats(): float { return $this->caratWeight / 1000; }
    public function getQuality(): ?string { return $this->quality; }
    public function setQuality(?string $q): self { $this->quality = $q; return $this; }
    public function getColor(): ?string { return $this->color; }
    public function setColor(?string $c): self { $this->color = $c; return $this; }
    public function getCertificateRef(): ?string { return $this->certificateRef; }
    public function setCertificateRef(?string $r): self { $this->certificateRef = $r; return $this; }
    public function getCostPrice(): int { return $this->costPrice; }
    public function setCostPrice(int $p): self { $this->costPrice = $p; return $this; }
    public function getSupplier(): ?Supplier { return $this->supplier; }
    public function setSupplier(?Supplier $s): self { $this->supplier = $s; return $this; }

    public function getLabel(): string
    {
        return sprintf('%s %.3f ct%s%s',
            $this->type->label(),
            $this->getCarats(),
            $this->quality ? ' '.$this->quality : '',
            $this->color ? ' '.$this->color : '',
        );
    }
}
