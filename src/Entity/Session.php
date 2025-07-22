<?php
namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'sessions')]
/**
 * Entité représentant une session de formation (créneau, participants, formateurs, etc.).
 */
class Session {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $formateur_responsable = null;

    // Plage horaire précise (date + heure)
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: 'La date et l\'heure de début sont requises')]
    private  ? \DateTimeInterface $dateDebut = null;

    #[ORM\Column(type : 'datetime')]
    #[Assert\NotBlank(message: 'La date et l\'heure de fin sont requises')]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'La date de fin doit être après la date de début')]
    private  ? \DateTimeInterface $dateFin = null;

    #[ORM\Column(length : 255)]
    #[Assert\NotBlank(message: 'Le lieu est requis')]
    private ?string $lieu = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['présentiel', 'distanciel', 'hybride'], message: 'Modalité invalide')]
    private string $modalite = 'présentiel';

    #[ORM\Column(length: 20)]
    private string $statut = 'prévue';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remarques = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centre $centre = null;

    // Participants (stagiaires)
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'session_participants')]
    private Collection $participants;

    // Formateurs associés (intervenants)
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'session_formateurs')]
    private Collection $formateurs;

    public function __construct() {
        $this->participants = new ArrayCollection();
        $this->formateurs   = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getFormation(): ?Formation {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;
        return $this;
    }

    public function getFormateurResponsable(): ?User {
        return $this->formateur_responsable;
    }

    public function setFormateurResponsable(?User $user): static
    {
        $this->formateur_responsable = $user;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getLieu(): ?string {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getModalite(): string {
        return $this->modalite;
    }

    public function setModalite(string $modalite): static
    {
        $this->modalite = $modalite;
        return $this;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getRemarques(): ?string {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;
        return $this;
    }

    public function isIsActive(): bool {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCentre(): ?Centre {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): static
    {
        $this->centre = $centre;
        return $this;
    }

    /**
     * Retourne la liste des participants (stagiaires) à la session.
     *
     * @return Collection<int, User> Liste des utilisateurs participants.
     */
    public function getParticipants(): Collection {
        return $this->participants;
    }

    /**
     * Ajoute un participant à la session.
     *
     * @param User $user Le participant à ajouter.
     * @return static
     */
    public function addParticipant(User $user): static {
        if (! $this->participants->contains($user)) {
            $this->participants->add($user);
        }
        return $this;
    }

    /**
     * Retire un participant de la session.
     *
     * @param User $user Le participant à retirer.
     * @return static
     */
    public function removeParticipant(User $user): static {
        $this->participants->removeElement($user);
        return $this;
    }

    /**
     * Retourne la liste des formateurs associés à la session.
     *
     * @return Collection<int, User> Liste des formateurs.
     */
    public function getFormateurs(): Collection {
        return $this->formateurs;
    }

    /**
     * Ajoute un formateur à la session.
     *
     * @param User $user Le formateur à ajouter.
     * @return static
     */
    public function addFormateur(User $user): static {
        if (! $this->formateurs->contains($user)) {
            $this->formateurs->add($user);
        }
        return $this;
    }

    /**
     * Retire un formateur de la session.
     *
     * @param User $user Le formateur à retirer.
     * @return static
     */
    public function removeFormateur(User $user): static {
        $this->formateurs->removeElement($user);
        return $this;
    }
}
