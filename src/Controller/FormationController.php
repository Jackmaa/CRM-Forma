<?php
namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/formation', name: 'formation_')]
class FormationController extends AbstractController {
    /**
     * Liste toutes les formations du centre de l’utilisateur connecté.
     *
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response La vue listant les formations.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(EntityManagerInterface $em): Response {
        $repo       = $em->getRepository(Formation::class);
        $formations = $repo->findBy([
            'centre' => $this->getUser()->getCentre(),
        ]);

        return $this->render('formation/formation.html.twig', [
            'formations' => $formations,
        ]);
    }

    /**
     * Même liste que index(), mais retourne le résultat au format JSON (API).
     *
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return JsonResponse La liste des formations au format JSON.
     */
    #[Route('/api', name: 'api', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function api(EntityManagerInterface $em): JsonResponse {
        $repo       = $em->getRepository(Formation::class);
        $formations = $repo->findBy([
            'centre' => $this->getUser()->getCentre(),
        ]);

        $data = array_map(fn(Formation $f) => [
            'id'          => $f->getId(),
            'title'       => $f->getTitre(),
            'description' => $f->getDescription(),
        ], $formations);

        return $this->json($data);
    }

    /**
     * Création d’une nouvelle formation.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response La vue du formulaire de création ou la redirection après succès.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function new (Request $request, EntityManagerInterface $em): Response {
        $formation = new Formation();
        $formation
            ->setCentre($this->getUser()->getCentre())
            ->setResponsable($this->getUser());

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($formation);
            $em->flush();

            $this->addFlash('success', 'Formation créée avec succès.');
            return $this->redirectToRoute('formation_index');
        }

        return $this->render('formation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche le détail d’une formation.
     *
     * @param Formation $formation L'entité formation à afficher.
     * @return Response La vue de détail de la formation.
     */
    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Formation $formation): Response {
        // Vérifie que la formation appartient bien au centre de l’utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé : formation hors de votre centre.');
        }

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    /**
     * Modification partielle ou affichage du formulaire d’édition.
     *
     * @param Request $request La requête HTTP.
     * @param Formation $formation L'entité formation à modifier.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response|JsonResponse La vue d'édition ou la réponse JSON après modification.
     */
    #[Route('/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): Response | JsonResponse {
        // Vérifie que la formation appartient bien au centre de l’utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé : formation hors de votre centre.');
        }

        if ($request->isMethod('GET')) {
            return $this->render('formation/edit.html.twig', [
                'formation' => $formation,
            ]);
        }

        $data = json_decode($request->getContent(), true);
        // Ne mettre à jour que les champs présents dans la requête
        foreach ([
            'titre', 'slug', 'thematique', 'niveau',
            'duree', 'tarif', 'prerequis',
            'description', 'modalites', 'objectifs',
        ] as $field) {
            if (isset($data[$field])) {
                $setter = 'set' . ucfirst($field);
                $formation->$setter($field === 'duree' ? (int) $data[$field] : $data[$field]);
            }
        }

        $em->flush();

        return $this->json([
            'status'    => 'ok',
            'formation' => [
                'id'          => $formation->getId(),
                'titre'       => $formation->getTitre(),
                'slug'        => $formation->getSlug(),
                'thematique'  => $formation->getThematique(),
                'niveau'      => $formation->getNiveau(),
                'duree'       => $formation->getDuree(),
                'tarif'       => $formation->getTarif(),
                'prerequis'   => $formation->getPrerequis(),
                'description' => $formation->getDescription(),
                'modalites'   => $formation->getModalites(),
                'objectifs'   => $formation->getObjectifs(),
            ],
        ]);
    }

    /**
     * Suppression d’une formation.
     *
     * @param Request $request La requête HTTP.
     * @param Formation $formation L'entité formation à supprimer.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response Redirige vers la liste des formations après suppression.
     */
    #[Route('/{id<\d+>}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): Response {
        // Vérifie que la formation appartient bien au centre de l’utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé : formation hors de votre centre.');
        }

        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
            $em->remove($formation);
            $em->flush();
            $this->addFlash('success', 'Formation supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('formation_index');
    }

    /**
     * Détail JSON d’une formation (API).
     *
     * @param Formation $formation L'entité formation à retourner.
     * @return JsonResponse Les informations de la formation au format JSON.
     */
    #[Route('/api/{id<\d+>}', name: 'api_detail', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function apiDetail(Formation $formation): JsonResponse {
        // Vérifie que la formation appartient bien au centre de l’utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé : formation hors de votre centre.');
        }

        return $this->json([
            'id'          => $formation->getId(),
            'title'       => $formation->getTitre(),
            'description' => $formation->getDescription(),
        ]);
    }
}
