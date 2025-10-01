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

/**
 * Contrôleur pour la gestion des utilisateurs (CRUD et API).
 */
final class UserController extends AbstractController {
    /**
     * Affiche la page principale des utilisateurs.
     *
     * @return Response La vue principale des utilisateurs.
     */
    #[Route('/user', name: 'app_user')]
    public function index(): Response {
        return $this->render('user/user.html.twig');
    }

    /**
     * Retourne la liste des utilisateurs du centre (API JSON).
     *
     * @param Request $request La requête HTTP.
     * @param UserRepository $userRepository Le repository des utilisateurs.
     * @return JsonResponse La liste des utilisateurs au format JSON.
     */
    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function getAllUsers(Request $request, UserRepository $userRepository): JsonResponse {
        $role   = $request->query->get('role');
        $centre = $this->getUser()->getCentre();

        // Si on a un filtre role, on l’applique, sinon on prend tout
        if ($role) {
            $users = $userRepository->findBy([
                'role'   => $role,
                'centre' => $centre,
            ]);
        } else {
            $users = $userRepository->findBy(['centre' => $centre]);
        }

        $formatted = array_map(fn($user) => [
            'id'       => $user->getId(),
            'fullname' => $user->getFullName(),
            'nom'      => $user->getNom(),
            'prenom'   => $user->getPrenom(),
            'email'    => $user->getEmail(),
            'role'     => $user->getRole()->value,
            'initials' => $user->getInitials(),
        ], $users);

        return $this->json($formatted);
    }

    /**
     * Crée un nouvel utilisateur via formulaire (admin centre).
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hash de mot de passe.
     * @param MailerInterface $mailer Le service d'envoi d'emails.
     * @return Response La vue du formulaire ou la redirection après succès.
     */
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

    /**
     * Retourne les paramètres de l'utilisateur courant (API JSON).
     *
     * @return JsonResponse Les paramètres utilisateur.
     */
    #[Route('/api/user/settings', name: 'api_user_get_settings', methods: ['GET'])]
    public function getSettings(): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $resp = $this->json([
            'username'           => $user->getPrenom(),
            'email'              => $user->getEmail(),
            'forcePasswordReset' => $user->getForcePasswordReset(),
        ]);

        // Anti-cache
        $resp->setPrivate();
        $resp->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $resp->headers->set('Pragma', 'no-cache');

        return $resp;
    }

    /**
     * Met à jour les paramètres de l'utilisateur courant (API JSON).
     *
     * @param Request $req La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $hasher Le service de hash de mot de passe.
     * @return JsonResponse Succès ou erreurs de validation.
     */
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

    /**
     * Retourne le détail d'un utilisateur (API JSON).
     *
     * @param User $user L'utilisateur à afficher.
     * @return JsonResponse Les informations de l'utilisateur.
     */
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

    /**
     * Met à jour un utilisateur (API JSON).
     *
     * @param User $user L'utilisateur à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $hasher Le service de hash de mot de passe.
     * @return JsonResponse Succès ou erreurs de validation.
     */
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

    /**
     * Affiche la page de détail d'un utilisateur.
     *
     * @param User $user L'utilisateur à afficher.
     * @return Response La vue de détail de l'utilisateur.
     */
    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response {
        if ($user->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Modifie un utilisateur via formulaire.
     *
     * @param Request $request La requête HTTP.
     * @param User $user L'utilisateur à modifier.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $hasher Le service de hash de mot de passe.
     * @return Response La vue d'édition ou la redirection après succès.
     */
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

    /**
     * Supprime un utilisateur.
     *
     * @param Request $req La requête HTTP.
     * @param User $user L'utilisateur à supprimer.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response Redirige vers la liste après suppression.
     */
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
