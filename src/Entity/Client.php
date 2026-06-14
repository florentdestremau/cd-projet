<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'clients')]
class Client implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    private string $displayName = '';

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $linkedUser = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $name): self
    {
        $this->displayName = $name;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $name): self
    {
        $this->companyName = $name;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $email): self
    {
        $this->contactEmail = $email;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $phone): self
    {
        $this->contactPhone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getLinkedUser(): ?User
    {
        return $this->linkedUser;
    }

    public function setLinkedUser(?User $user): self
    {
        $this->linkedUser = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return $this->displayName;
    }
}
