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
}
