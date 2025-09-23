<?php
namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AuthTokenController extends AbstractController {
    #[Route('/auth/jwt', name: 'auth_issue_jwt', methods: ['POST'])]
    public function issue(
        Request $request,
        JWTTokenManagerInterface $jwtManager,
        CsrfTokenManagerInterface $csrf
    ): JsonResponse {
        // exiger un utilisateur de session (form_login)
        $user = $this->getUser();
        if (! $user) {
            return $this->json(['message' => 'Non authentifié (session)'], 401);
        }

        // vérifier le CSRF
        $provided = $request->headers->get('X-CSRF-TOKEN', '');
        if (! $csrf->isTokenValid(new CsrfToken('issue_jwt', $provided))) {
            return $this->json(['message' => 'CSRF invalide'], 403);
        }

        // générer un JWT pour l'utilisateur courant
        $token = $jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}
