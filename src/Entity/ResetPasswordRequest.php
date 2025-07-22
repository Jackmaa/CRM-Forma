<?php
namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * Entité représentant une demande de réinitialisation de mot de passe.
 *
 * Utilisée par le bundle SymfonyCasts ResetPassword.
 */
#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface {
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Construit une nouvelle demande de réinitialisation de mot de passe.
     *
     * @param User $user L'utilisateur concerné.
     * @param \DateTimeInterface $expiresAt Date d'expiration de la demande.
     * @param string $selector Sélecteur unique.
     * @param string $hashedToken Jeton hashé.
     */
    public function __construct(User $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken) {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Retourne l'identifiant de la demande.
     *
     * @return int|null L'identifiant.
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur concerné par la demande.
     *
     * @return User L'utilisateur.
     */
    public function getUser(): User {
        return $this->user;
    }
}
