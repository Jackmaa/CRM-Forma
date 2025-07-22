<?php
namespace App\Controller;

use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la gestion des sessions (CRUD et API).
 */
#[Route('/session')]
#[IsGranted('ROLE_ADMIN_CENTRE')]
class SessionController extends AbstractController {
    // --------------------------------------------------------------------------
    // 1) CRUD classique (Twig)
    // --------------------------------------------------------------------------

    /**
     * Affiche la liste des sessions (vue Twig).
     *
     * @return Response La vue listant les sessions.
     */
    #[Route('/', name: 'session_index', methods: ['GET'])]
    public function index(): Response {
        // Rend une page Twig avec <div {{ vue_component('Session/SessionList') }}>
        return $this->render('session/index.html.twig');
    }

    /**
     * Crée une nouvelle session (formulaire).
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response La vue du formulaire ou la redirection après succès.
     */
    #[Route('/new', name: 'session_new', methods: ['GET', 'POST'])]
    public function new (Request $request, EntityManagerInterface $em): Response {
        $session = new Session();
        // Associe automatiquement le centre de l’admin courant
        $session->setCentre($this->getUser()->getCentre());

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($session);
            $em->flush();
            $this->addFlash('success', 'Session créée avec succès.');

            return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
        }

        return $this->render('session/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche le détail d'une session.
     *
     * @param Session $session L'entité session à afficher.
     * @return Response La vue de détail de la session.
     */
    #[Route(
        '/{id}',
        name: 'session_show',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function show(Session $session): Response {
        // Sécurité : vérifie que la session appartient au même centre que l'utilisateur
        if ($session->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * Modifie une session existante (formulaire).
     *
     * @param Request $request La requête HTTP.
     * @param Session $session L'entité session à modifier.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response La vue d'édition ou la redirection après succès.
     */
    #[Route(
        '/{id}/edit',
        name: 'session_edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    public function edit(
        Request $request,
        Session $session,
        EntityManagerInterface $em
    ): Response {
        if ($session->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Session mise à jour.');

            return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
        }

        return $this->render('session/edit.html.twig', [
            'form'    => $form->createView(),
            'session' => $session,
        ]);
    }

    /**
     * Supprime une session.
     *
     * @param Request $request La requête HTTP.
     * @param Session $session L'entité session à supprimer.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response Redirige vers la liste après suppression.
     */
    #[Route(
        '/{id}/delete',
        name: 'session_delete',
        methods: ['POST'],
        requirements: ['id' => '\d+']
    )]
    public function delete(Request $request, Session $session, EntityManagerInterface $em): Response {
        if ($session->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_session_' . $session->getId(), $request->request->get('_token'))) {
            $em->remove($session);
            $em->flush();
            $this->addFlash('success', 'Session supprimée.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('session_index');
    }

    // --------------------------------------------------------------------------
    // 2) API (JSON)
    // --------------------------------------------------------------------------

    /**
     * Retourne la liste des sessions du centre de l'utilisateur (API JSON).
     *
     * @param SessionRepository $repo Le repository des sessions.
     * @return JsonResponse La liste des sessions au format JSON.
     */
    #[Route('/api', name: 'api_session_list', methods: ['GET'])]
    public function apiList(SessionRepository $repo): JsonResponse {
        $sessions = $repo->findBy(['centre' => $this->getUser()->getCentre()]);
        $data     = array_map(fn(Session $s) => [
            'id'          => $s->getId(),
            'formationId' => $s->getFormation()->getId(),
            'titre'       => $s->getFormation()->getTitre(),
            'dateDebut'   => $s->getDateDebut()?->format(\DateTime::ATOM),
            'dateFin'     => $s->getDateFin()?->format(\DateTime::ATOM),
            'statut'      => $s->getStatut(),
            'isActive'    => $s->isIsActive(),
        ], $sessions);

        return $this->json($data);
    }

    /**
     * Crée une session via l'API (JSON).
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return JsonResponse Succès ou erreurs de validation.
     */
    #[Route('/api', name: 'api_session_create', methods: ['POST'])]
    public function apiCreate(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data    = json_decode($request->getContent(), true);
        $session = new Session();
        $session->setCentre($this->getUser()->getCentre());

        $form = $this->createForm(SessionType::class, $session, [
            'method'             => 'POST',
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data, false);

        if (! $form->isValid()) {
            return $this->json($this->getFormErrors($form->getErrors(true)), 400);
        }

        $em->persist($session);
        $em->flush();

        return $this->json(['success' => true, 'id' => $session->getId()], 201);
    }

    /**
     * Met à jour une session via l'API (JSON).
     *
     * @param Session $session L'entité session à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return JsonResponse Succès ou erreurs de validation.
     */
    #[Route(
        '/api/{id}',
        name: 'api_session_update',
        methods: ['PATCH'],
        requirements: ['id' => '\d+']
    )]
    public function apiUpdate(
        Session $session,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($session->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(SessionType::class, $session, [
            'method'             => 'PATCH',
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data, false);

        if (! $form->isValid()) {
            return $this->json($this->getFormErrors($form->getErrors(true)), 400);
        }

        $em->flush();
        return $this->json(['success' => true]);
    }

    /**
     * Helper pour extraire les messages d’erreur du FormType.
     *
     * @param iterable $errors Les erreurs du formulaire.
     * @return array<string, string[]> Tableau des erreurs par champ.
     */
    private function getFormErrors(iterable $errors): array {
        $out = [];
        foreach ($errors as $error) {
            $field         = $error->getOrigin()->getName();
            $out[$field][] = $error->getMessage();
        }
        return $out;
    }
}
