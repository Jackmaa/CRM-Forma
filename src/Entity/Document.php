<?php
namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'document')]
#[ORM\Index(columns: ['type', 'number'], name: 'idx_doc_type_number')]
class Document {
    public const TYPE_DEVIS       = 'DEVIS';
    public const TYPE_CONVENTION  = 'CONVENTION';
    public const STATUS_GENERATED = 'GENERATED';
    public const STATUS_SENT      = 'SENT';
    public const STATUS_SIGNED    = 'SIGNED';
    public const STATUS_ARCHIVED  = 'ARCHIVED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $type;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $number;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status = self::STATUS_GENERATED;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $filePath = null;

    // Adapte la classe Centre si ton namespace diffère
    #[ORM\ManyToOne(targetEntity: \App\Entity\Centre::class)]
    private ?object $centre = null;

    #[ORM\Column(type: Types::JSON)]
    private array $snapshot = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    private ?object $createdBy = null;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int {return $this->id;}

    public function getType(): string {return $this->type;}
    public function setType(string $type): self {$this->type = $type;return $this;}

    public function getNumber(): string {return $this->number;}
    public function setNumber(string $number): self {$this->number = $number;return $this;}

    public function getStatus(): string {return $this->status;}
    public function setStatus(string $status): self {$this->status = $status;return $this;}

    public function getFilePath(): ?string {return $this->filePath;}
    public function setFilePath(?string $filePath): self {$this->filePath = $filePath;return $this;}

    public function getCentre(): ?object {return $this->centre;}
    public function setCentre(?object $centre): self {$this->centre = $centre;return $this;}

    public function getSnapshot(): array {return $this->snapshot;}
    public function setSnapshot(array $snapshot): self {$this->snapshot = $snapshot;return $this;}

    public function getCreatedAt(): \DateTimeImmutable {return $this->createdAt;}
    public function setCreatedAt(\DateTimeImmutable $createdAt): self {$this->createdAt = $createdAt;return $this;}

    public function getCreatedBy(): ?object {return $this->createdBy;}
    public function setCreatedBy(?object $createdBy): self {$this->createdBy = $createdBy;return $this;}
}
