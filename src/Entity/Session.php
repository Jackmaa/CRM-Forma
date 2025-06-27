<?php
namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'sessions')]
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

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'La date de début est requise')]
    private  ? \DateTimeInterface $date_debut = null;

    #[ORM\Column(type : 'date')]
    #[Assert\NotBlank(message: 'La date de fin est requise')]
    private  ? \DateTimeInterface $date_fin = null;

    #[ORM\Column(length : 255)]
    #[Assert\NotBlank(message: 'Le lieu est requis')]
    private ?string $lieu = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'La modalité est requise')]
    private ?string $modalite = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remarques = null;

    #[ORM\Column]
    private bool $is_active = true;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centre $centre = null;

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

    public function setFormateurResponsable(?User $formateur_responsable): static
    {
        $this->formateur_responsable = $formateur_responsable;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;
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

    public function getModalite(): ?string {
        return $this->modalite;
    }

    public function setModalite(string $modalite): static
    {
        $this->modalite = $modalite;
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
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;
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
}
