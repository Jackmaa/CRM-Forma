<?php
namespace App\Entity;

use App\Repository\CentreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CentreRepository::class)]
#[ORM\Table(name: 'centres')]
/**
 * Entité représentant un centre de formation (structure, utilisateurs, formations, sessions).
 */
class Centre {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du centre est requis')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 14, unique: true)]
    #[Assert\NotBlank(message: 'Le SIRET est requis')]
    #[Assert\Length(
        min: 14,
        max: 14,
        exactMessage: 'Le SIRET doit contenir exactement {{ limit }} chiffres'
    )]
    #[Assert\Regex(pattern: '/^\d{14}$/', message: 'Le SIRET doit contenir uniquement des chiffres')]
    private ?string $siret = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'L\'adresse est requise')]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'L\'email n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est requis')]
    private ?string $telephone = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logo_url = null;

    #[ORM\OneToMany(mappedBy: 'centre', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'centre', targetEntity: Formation::class)]
    private Collection $formations;

    #[ORM\OneToMany(mappedBy: 'centre', targetEntity: Session::class)]
    private Collection $sessions;

    public function __construct() {
        $this->users      = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->sessions   = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getSiret(): ?string {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;
        return $this;
    }

    public function getAdresse(): ?string {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getLogoUrl(): ?string {
        return $this->logo_url;
    }

    public function setLogoUrl(?string $logo_url): static
    {
        $this->logo_url = $logo_url;
        return $this;
    }

    /**
     * Retourne la liste des utilisateurs du centre.
     *
     * @return Collection<int, User> Liste des utilisateurs.
     */
    public function getUsers(): Collection {
        return $this->users;
    }

    /**
     * Ajoute un utilisateur au centre.
     *
     * @param User $user L'utilisateur à ajouter.
     * @return static
     */
    public function addUser(User $user): static {
        if (! $this->users->contains($user)) {
            $this->users->add($user);
            $user->setCentre($this);
        }
        return $this;
    }

    /**
     * Retire un utilisateur du centre.
     *
     * @param User $user L'utilisateur à retirer.
     * @return static
     */
    public function removeUser(User $user): static {
        if ($this->users->removeElement($user)) {
            if ($user->getCentre() === $this) {
                $user->setCentre(null);
            }
        }
        return $this;
    }

    /**
     * Retourne la liste des formations du centre.
     *
     * @return Collection<int, Formation> Liste des formations.
     */
    public function getFormations(): Collection {
        return $this->formations;
    }

    /**
     * Ajoute une formation au centre.
     *
     * @param Formation $formation La formation à ajouter.
     * @return static
     */
    public function addFormation(Formation $formation): static {
        if (! $this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->setCentre($this);
        }
        return $this;
    }

    /**
     * Retire une formation du centre.
     *
     * @param Formation $formation La formation à retirer.
     * @return static
     */
    public function removeFormation(Formation $formation): static {
        if ($this->formations->removeElement($formation)) {
            if ($formation->getCentre() === $this) {
                $formation->setCentre(null);
            }
        }
        return $this;
    }

    /**
     * Retourne la liste des sessions du centre.
     *
     * @return Collection<int, Session> Liste des sessions.
     */
    public function getSessions(): Collection {
        return $this->sessions;
    }

    /**
     * Ajoute une session au centre.
     *
     * @param Session $session La session à ajouter.
     * @return static
     */
    public function addSession(Session $session): static {
        if (! $this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCentre($this);
        }
        return $this;
    }

    /**
     * Retire une session du centre.
     *
     * @param Session $session La session à retirer.
     * @return static
     */
    public function removeSession(Session $session): static {
        if ($this->sessions->removeElement($session)) {
            if ($session->getCentre() === $this) {
                $session->setCentre(null);
            }
        }
        return $this;
    }
}