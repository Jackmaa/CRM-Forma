<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController {
    #[Route('/user', name: 'app_user')]
    public function index(): Response {
        return $this->render('user/user.html.twig');
    }

    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository): JsonResponse {
        $users = $userRepository->findAll();

        $formatted = array_map(function ($user) {
            return [
                'id'       => $user->getId(),
                'fullname' => $user->getFullName(),
                'nom'      => $user->getNom(),
                'prenom'   => $user->getPrenom(),
                'email'    => $user->getEmail(),
                'initials' => $user->getInitials(),
            ];
        }, $users);

        return $this->json($formatted);
    }
}
