<?php
namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
#[ORM\Table(name: 'formations')]
/**
 * Entité représentant une formation (catalogue, sessions, etc.).
 */
class Formation {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est requis')]
    private ?string $titre = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est requis')]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La thématique est requise')]
    private ?string $thematique = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le niveau est requis')]
    private ?string $niveau = null;

    #[ORM\Column(type: 'json')]
    private array $modalites = [];

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Les prérequis sont requis')]
    private ?string $prerequis = null;

    #[ORM\Column(type: 'json')]
    private array $objectifs = [];

    #[ORM\Column]
    #[Assert\Positive(message: 'La durée doit être positive')]
    private int $duree = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\PositiveOrZero(message: 'Le tarif doit être positif ou zéro')]
    private string $tarif = '0.00';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description est requise')]
    private ?string $description = null;

    #[ORM\Column]
    private  ? \DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy : 'formations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centre $centre = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $responsable = null;

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: Session::class)]
    private Collection $sessions;

    public function __construct() {
        $this->sessions   = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->modalites  = [];
        $this->objectifs  = [];
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getThematique(): ?string {
        return $this->thematique;
    }

    public function setThematique(string $thematique): static
    {
        $this->thematique = $thematique;
        return $this;
    }

    public function getNiveau(): ?string {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getModalites(): array {
        return $this->modalites;
    }

    public function setModalites(array $modalites): static
    {
        $this->modalites = $modalites;
        return $this;
    }

    public function getPrerequis(): ?string {
        return $this->prerequis;
    }

    public function setPrerequis(string $prerequis): static
    {
        $this->prerequis = $prerequis;
        return $this;
    }

    public function getObjectifs(): array {
        return $this->objectifs;
    }

    public function setObjectifs(array $objectifs): static
    {
        $this->objectifs = $objectifs;
        return $this;
    }

    public function getDuree(): int {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getTarif(): string {
        return $this->tarif;
    }

    public function setTarif(string $tarif): static
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->created_at;
    }

    public function getCentre(): ?Centre {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): static
    {
        $this->centre = $centre;
        return $this;
    }

    public function getResponsable(): ?User {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): static
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * Retourne la liste des sessions associées à cette formation.
     *
     * @return Collection<int, Session> Liste des sessions.
     */
    public function getSessions(): Collection {
        return $this->sessions;
    }

    /**
     * Ajoute une session à la formation.
     *
     * @param Session $session La session à ajouter.
     * @return static
     */
    public function addSession(Session $session): static {
        if (! $this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setFormation($this);
        }
        return $this;
    }

    /**
     * Retire une session de la formation.
     *
     * @param Session $session La session à retirer.
     * @return static
     */
    public function removeSession(Session $session): static {
        if ($this->sessions->removeElement($session)) {
            if ($session->getFormation() === $this) {
                $session->setFormation(null);
            }
        }
        return $this;
    }
}
