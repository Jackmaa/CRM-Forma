<?php
namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: App\Repository\ActivityRepository::class)]
class Activity {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['activity:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['activity:read'])]
    private string $action;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['activity:read'])]
    private string $entityName;

    #[ORM\Column(type: 'integer')]
    #[Groups(['activity:read'])]
    private int $entityId;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['activity:read'])]
    private string $description;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['activity:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['activity:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getAction(): string {
        return $this->action;
    }

    public function setAction(string $action): self {
        $this->action = $action;
        return $this;
    }

    public function getEntityName(): string {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self {
        $this->entityName = $entityName;
        return $this;
    }

    public function getEntityId(): int {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self {
        $this->entityId = $entityId;
        return $this;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function setUser(?User $user): self {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable {
        return $this->createdAt;
    }
}
