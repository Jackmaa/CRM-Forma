<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
                'role'     => $user->getRole()->value,
            ];
        }, $users);

        return $this->json($formatted);
    }

    #[Route('/user/new', name: 'user_new')]
    public function new (
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_CENTRE');

        $user = new User();
        // assignation automatique du Centre de l'admin courant
        $user->setCentre($this->getUser()->getCentre());
        // champs d’état par défaut
        $user->setIsActive(true);
        $user->setForcePasswordReset(true);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // génération et hash du mot de passe
            $plainPassword = bin2hex(random_bytes(6));
            $hashed        = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();

            // envoi du mail avec le mot de passe
            $email = (new Email())
                ->from('no-reply@toncrm.com')
                ->to($user->getEmail())
                ->subject('Bienvenue sur CRM-Forma')
                ->html(sprintf(
                    'Bonjour %s,<br><br>Votre compte a été créé. '
                    . 'Voici votre mot de passe temporaire : <strong>%s</strong><br>'
                    . 'Merci de le modifier dès votre première connexion.',
                    $user->getPrenom(),
                    $plainPassword
                ))
            ;
            $mailer->send($email);

            $this->addFlash('success', 'Utilisateur créé et email envoyé.');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/api/user/settings', name: 'api_user_get_settings', methods: ['GET'])]
    public function getSettings(): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        return $this->json([
            'username'           => $user->getPrenom(),
            'email'              => $user->getEmail(),
            'forcePasswordReset' => $user->getForcePasswordReset(),
        ]);
    }

    #[Route('/api/user/settings', name: 'api_user_update_settings', methods: ['PUT'])]
    public function updateSettings(
        Request $req,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $data = json_decode($req->getContent(), true);

        // Toujours pouvoir mettre à jour email / username
        $user->setPrenom($data['username']);
        $user->setEmail($data['email']);

        // Si on a un nouveau mot de passe
        if (! empty($data['password'])) {
            $hashed = $hasher->hashPassword($user, $data['password']);
            $user->setPassword($hashed);
            // on désactive le forçage
            $user->setForcePasswordReset(false);
        }

        $em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/api/user/{id}', name: 'user_api_detail', methods: ['GET'])]
    public function apiDetail(User $user): JsonResponse {
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }
        return $this->json([
            'id'       => $user->getId(),
            'prenom'   => $user->getPrenom(),
            'nom'      => $user->getNom(),
            'email'    => $user->getEmail(),
            'role'     => $user->getRole()->value,
            'isActive' => (bool) $user->isActive(),
        ]);
    }

    #[Route('/api/user/{id}', name: 'api_user_update', methods: ['PATCH'])]
    public function apiUpdate(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        // Sécurité : même centre
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        // Champs modifiables
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['role'])) {
            $user->setRole(\App\Enum\UserRole::from($data['role']));
        }
        if (isset($data['isActive'])) {
            $user->setIsActive((bool) $data['isActive']);
        }
        // mot de passe si envoyé
        if (! empty($data['password'])) {
            $user->setPassword($hasher->hashPassword($user, $data['password']));
            $user->setForcePasswordReset(false);
        }

        $em->flush();
        return $this->json([
            'success' => true,
            // renvoie éventuellement la nouvelle version de la ressource
            'user'    => [
                'prenom'   => $user->getPrenom(),
                'nom'      => $user->getNom(),
                'email'    => $user->getEmail(),
                'role'     => $user->getRole()->value,
                'isActive' => $user->isActive(),
            ],
        ]);
    }

    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response {
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user);
        // Optionnel : champ mot-de-passe non mappé pour changer l'existant
        $form->add('plainPassword', PasswordType::class, [
            'mapped'   => false,
            'required' => false,
            'label'    => 'Nouveau mot de passe',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $new = $form->get('plainPassword')->getData();
            if ($new) {
                $user->setPassword($hasher->hashPassword($user, $new));
                $user->setForcePasswordReset(false);
            }
            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $req, User $user, EntityManagerInterface $em): Response {
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $req->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('user_index');
    }
}
