<?php
// src/Controller/ImportUserController.php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\CentreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/import')]
class ImportUserController extends AbstractController {
    #[Route('/users', name: 'import_users_csv', methods: ['POST'])]
    public function import(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        CentreRepository $centreRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data)) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        $currentUser = $this->getUser();
        $centre      = $currentUser->getCentre(); // centre obligatoire ici

        $createdUsers = [];

        foreach ($data as $userData) {
            if (empty($userData['email']) || empty($userData['nom']) || empty($userData['prenom'])) {
                continue;
            }

            $user = new User();
            $user->setEmail($userData['email']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setCentre($centre);
            $user->setRole(UserRole::STAGIAIRE);
            $user->setPassword($hasher->hashPassword($user, 'stagiaire123'));

            $em->persist($user);
            $createdUsers[] = [
                'id'       => $user->getId(),
                'initials' => $user->getInitials(),
                'email'    => $user->getEmail(),
                'nom'      => $user->getNom(),
                'prenom'   => $user->getPrenom(),

            ];
        }

        $em->flush();

        return $this->json([
            'message' => count($createdUsers) . ' utilisateurs créés',
            'users'   => $createdUsers,
        ]);
    }
}
