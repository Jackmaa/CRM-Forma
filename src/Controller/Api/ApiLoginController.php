<?php
namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ApiLoginController {
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(): JsonResponse {
        // Jamais exécuté : intercepté par json_login (firewall)
        return new JsonResponse(['message' => 'Handled by LexikJWT'], 400);
    }
}
