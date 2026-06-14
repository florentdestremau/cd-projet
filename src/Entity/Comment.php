<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
#[ORM\Index(name: 'comments_project_created_idx', columns: ['project_id', 'created_at'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $body = '';

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'comment_mentions')]
    private Collection $mentions;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $editedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->mentions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $project): self { $this->project = $project; return $this; }
    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $user): self { $this->author = $user; return $this; }
    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getEditedAt(): ?\DateTimeImmutable { return $this->editedAt; }
    public function markEdited(): self { $this->editedAt = new \DateTimeImmutable(); return $this; }

    /** @return Collection<int, User> */
    public function getMentions(): Collection { return $this->mentions; }

    public function addMention(User $user): self
    {
        if (!$this->mentions->contains($user)) {
            $this->mentions->add($user);
        }

        return $this;
    }

    public function clearMentions(): self
    {
        $this->mentions->clear();

        return $this;
    }
}
