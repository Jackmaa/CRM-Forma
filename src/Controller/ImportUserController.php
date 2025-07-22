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

/**
 * Contrôleur pour l'import d'utilisateurs en masse (API).
 */
#[Route('/api/import')]
class ImportUserController extends AbstractController {
    /**
     * Importe des utilisateurs à partir d'un tableau JSON (API).
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $hasher Le service de hash de mot de passe.
     * @param CentreRepository $centreRepo Le repository des centres.
     * @return JsonResponse Résultat de l'import (succès ou erreurs).
     */
    #[Route('/users', name: 'import_users_csv', methods: ['POST'])]
    public function import(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        CentreRepository $centreRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data)) {
            return $this->json(['error' => 'Payload invalide'], 400);
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
