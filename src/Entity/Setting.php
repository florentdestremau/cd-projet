<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'settings')]
class Setting
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 80)]
        private string $key,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $value = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
