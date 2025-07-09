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

#[Route('/session')]
#[IsGranted('ROLE_ADMIN_CENTRE')]
class SessionController extends AbstractController {
    // --------------------------------------------------------------------------
    // 1) CRUD classique (Twig)
    // --------------------------------------------------------------------------

    #[Route('/', name: 'session_index', methods: ['GET'])]
    public function index(): Response {
        // Rend une page Twig avec <div {{ vue_component('Session/SessionList') }}>
        return $this->render('session/index.html.twig');
    }

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

    #[Route('/{id}', name: 'session_show', methods: ['GET'])]
    public function show(Session $session): Response {
        // Sécurité : même centre
        if ($session->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/{id}/edit', name: 'session_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}/delete', name: 'session_delete', methods: ['POST'])]
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
    /**
     * GET  /api/session
     * POST /api/session
     */
    #[Route('/api', name: 'api_session_list', methods: ['GET'])]
    #[Route('/api', name: 'api_session_create', methods: ['POST'])]
    public function apiListAndCreate(
        Request $request,
        SessionRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($request->isMethod('GET')) {
            // LIST
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

        // CREATE
        $data    = json_decode($request->getContent(), true);
        $session = new Session();
        $session->setCentre($this->getUser()->getCentre());

        $form = $this->createForm(SessionType::class, $session, [
            'method'             => 'POST',
            'csrf_protection'    => false, // Pas de CSRF pour l'API
            'allow_extra_fields' => true,  // Permet les champs additionnels
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
     * PATCH /api/session/{id}
     */
    #[Route('/api/{id}', name: 'api_session_update', methods: ['PATCH'])]
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
            'csrf_protection'    => false, // Pas de CSRF pour l'API
            'allow_extra_fields' => true,  // Permet de gérer les champs non définis dans le formulaire
        ]);
        $form->submit($data, false);
        if (! $form->isValid()) {
            return $this->json($this->getFormErrors($form->getErrors(true)), 400);
        }

        $em->flush();
        return $this->json(['success' => true]);
    }

/**
 * Helper pour extraire les messages d’erreur du FormType
 *
 * @param iterable $errors Instance de FormErrorIterator ou tout itérable de FormError
 * @return array<string, string[]>
 */
    private function getFormErrors(iterable $errors): array {
        $out = [];
        foreach ($errors as $error) {
            // Récupère le nom du champ à partir de l'erreur de formulaire
            $field         = $error->getOrigin()->getName();
            $out[$field][] = $error->getMessage();
        }
        return $out;
    }
}
