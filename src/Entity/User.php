<?php
namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
/**
 * Entité représentant un utilisateur du CRM (stagiaire, formateur, admin, etc.).
 *
 * Gère l'authentification, les rôles, et les relations avec le centre.
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'L\'email n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est requis')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est requis')]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', enumType: UserRole::class)]
    #[Assert\NotNull(message: 'Le rôle est requis')]
    private ?UserRole $role = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centre $centre = null;

    #[ORM\Column]
    private  ? \DateTimeImmutable $created_at = null;

    #[ORM\Column(type : 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'boolean')]
    private bool $forcePasswordReset = true;

    public function __construct() {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Retourne l'identifiant unique de l'utilisateur (email).
     *
     * @see UserInterface
     * @return string L'email de l'utilisateur.
     */
    public function getUserIdentifier(): string {
        return (string) $this->email;
    }

    /**
     * Retourne les rôles Symfony de l'utilisateur (ROLE_USER, ROLE_ADMIN_CENTRE, etc.).
     *
     * @see UserInterface
     * @return array Liste des rôles Symfony.
     */
    public function getRoles(): array {
        $roles = ['ROLE_USER'];

        if ($this->role) {
            $roles[] = 'ROLE_' . $this->role->value;
        }

        return array_unique($roles);
    }

    /**
     * Retourne le mot de passe hashé de l'utilisateur.
     *
     * @see PasswordAuthenticatedUserInterface
     * @return string Mot de passe hashé.
     */
    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Efface les données sensibles temporaires (non utilisé ici).
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void {
        // Si vous stockez des données sensibles temporaires, effacez-les ici
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * Retourne le nom complet de l'utilisateur (prénom + nom).
     *
     * @return string Nom complet.
     */
    public function getFullName(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Retourne les initiales de l'utilisateur (ex: JD pour Jean Dupont).
     *
     * @return string Initiales.
     */
    public function getInitials(): string {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getRole(): ?UserRole {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;
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

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->created_at;
    }

    /**
     * Vérifie si l'utilisateur est formateur.
     *
     * @return bool Vrai si formateur.
     */
    public function isFormateur() : bool {
        return $this->role === UserRole::FORMATEUR;
    }

    /**
     * Vérifie si l'utilisateur est administrateur de centre.
     *
     * @return bool Vrai si admin centre.
     */
    public function isAdminCentre(): bool {
        return $this->role === UserRole::ADMIN_CENTRE;
    }

    /**
     * Vérifie si l'utilisateur est stagiaire.
     *
     * @return bool Vrai si stagiaire.
     */
    public function isStagiaire(): bool {
        return $this->role === UserRole::STAGIAIRE;
    }

    /**
     * Vérifie si l'utilisateur est assistant.
     *
     * @return bool Vrai si assistant.
     */
    public function isAssistant(): bool {
        return $this->role === UserRole::ASSISTANT;
    }

    /**
     * Indique si le compte utilisateur est actif.
     *
     * @return bool Vrai si actif.
     */
    public function isActive(): bool {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Indique si l'utilisateur doit forcer la réinitialisation de son mot de passe.
     *
     * @return bool Vrai si le reset est forcé.
     */
    public function getForcePasswordReset(): bool {
        return $this->forcePasswordReset;
    }

    public function setForcePasswordReset(bool $forcePasswordReset): self {
        $this->forcePasswordReset = $forcePasswordReset;
        return $this;
    }
}