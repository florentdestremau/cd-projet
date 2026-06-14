<?php
declare(strict_types=1);
namespace App\Entity;

use App\Enum\SupplierSpecialty;
use App\Repository\SupplierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
#[ORM\Table(name: 'suppliers')]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $name = '';

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 20, enumType: SupplierSpecialty::class)]
    private SupplierSpecialty $specialty = SupplierSpecialty::OTHER;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $n): self { $this->name = $n; return $this; }
    public function getContactEmail(): ?string { return $this->contactEmail; }
    public function setContactEmail(?string $e): self { $this->contactEmail = $e; return $this; }
    public function getContactPhone(): ?string { return $this->contactPhone; }
    public function setContactPhone(?string $p): self { $this->contactPhone = $p; return $this; }
    public function getSpecialty(): SupplierSpecialty { return $this->specialty; }
    public function setSpecialty(SupplierSpecialty $s): self { $this->specialty = $s; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $n): self { $this->notes = $n; return $this; }
    public function __toString(): string { return $this->name; }
}
