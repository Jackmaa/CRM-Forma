<?php
// src/Controller/FormationController.php
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

#[Route('/formation')]
#[IsGranted('ROLE_USER')]
/**
 * Controller for managing formations.
 *
 * This controller handles the display, creation, and API access for formations.
 */
class FormationController extends AbstractController {
    #[Route('/', name: 'formation_index', methods: ['GET'])]
    /**
     * Displays the list of formations for the current user's centre.
     *
     * This method retrieves all formations associated with the user's centre
     * and renders them in a view.
     *
     * @param EntityManagerInterface $em The entity manager for database operations.
     * @return Response The response object with the rendered view.
     */
    public function index(EntityManagerInterface $em): Response {
        $repo       = $em->getRepository(Formation::class);
        $formations = $repo->findBy(['centre' => $this->getUser()->getCentre()]);

        return $this->render('formation/formation.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/api', name: 'formation_api', methods: ['GET'])]
    /**
     * Provides an API endpoint to retrieve formations in JSON format.
     *
     * This method returns a JSON response containing the list of formations
     * associated with the user's centre, including their ID, title, and description.
     *
     * @param EntityManagerInterface $em The entity manager for database operations.
     * @return JsonResponse The JSON response with the list of formations.
     */
    public function api(EntityManagerInterface $em): JsonResponse {
        $repo       = $em->getRepository(Formation::class);
        $formations = $repo->findBy(['centre' => $this->getUser()->getCentre()]);

        $data = array_map(fn(Formation $f) => [
            'id'          => $f->getId(),
            'title'       => $f->getTitre(),       // mappe votre champ titre → title
            'description' => $f->getDescription(), // mappe description
        ], $formations);

        return $this->json($data, 200, [], []);
    }

    #[Route('/{id}', name: 'formation_show', methods: ['GET'])]
    /**
     * Displays the details of a specific formation.
     *
     * This method retrieves a formation by its ID and renders its details in a view.
     *
     * @param Formation $formation The formation entity to be displayed.
     * @return Response The response object with the rendered view.
     */
    public function show(Formation $formation): Response {
        // Vérification que la formation appartient au centre de l'utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette formation.');
        }

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/new', name: 'formation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    /**
     * Creates a new formation.
     *
     * This method handles the creation of a new formation, automatically assigning
     * the current user's centre and responsible person.
     *
     * @param Request $request The HTTP request object.
     * @param EntityManagerInterface $em The entity manager for database operations.
     * @return Response The response object with the rendered form view or redirection.
     */
    public function new (Request $request, EntityManagerInterface $em): Response {
        $formation = new Formation();
        // Affectation automatique
        $formation->setCentre($this->getUser()->getCentre());
        $formation->setResponsable($this->getUser());

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

    #[Route('/{id}/edit', name: 'formation_edit', methods: ['GET', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    /**
     * Edits an existing formation.
     *
     * This method allows the user to edit the details of a formation.
     * It checks if the formation belongs to the user's centre before allowing edits.
     *
     * @param Request $request The HTTP request object.
     * @param Formation $formation The formation entity to be edited.
     * @param EntityManagerInterface $em The entity manager for database operations.
     * @return JsonResponse|Response The response object with the rendered form view or JSON response.
     */
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): JsonResponse | Response {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé.');
        }

        if ($request->isMethod('GET')) {
            return $this->render('formation/edit.html.twig', [
                'formation' => $formation,
            ]);
        }

        $data = json_decode($request->getContent(), true);

        // Met à jour uniquement si la clé existe
        if (array_key_exists('titre', $data)) {
            $formation->setTitre($data['titre']);
        }

        if (array_key_exists('slug', $data)) {
            $formation->setSlug($data['slug']);
        }

        if (array_key_exists('thematique', $data)) {
            $formation->setThematique($data['thematique']);
        }

        if (array_key_exists('niveau', $data)) {
            $formation->setNiveau($data['niveau']);
        }

        if (array_key_exists('duree', $data)) {
            $formation->setDuree((int) $data['duree']);
        }

        if (array_key_exists('tarif', $data)) {
            $formation->setTarif((string) $data['tarif']);
        }

        if (array_key_exists('prerequis', $data)) {
            $formation->setPrerequis($data['prerequis']);
        }

        if (array_key_exists('description', $data)) {
            $formation->setDescription($data['description']);
        }

        if (array_key_exists('modalites', $data)) {
            $formation->setModalites($data['modalites']);
        }

        if (array_key_exists('objectifs', $data)) {
            $formation->setObjectifs($data['objectifs']);
        }

        $em->flush();

        return $this->json(['status' => 'ok',
            'formation'                  => [
                'id'          => $formation->getId(),
                'titre'       => $formation->getTitre(),
                'slug'        => $formation->getSlug(),
                'thematique'  => $formation->getThematique(),
                'niveau'      => $formation->getNiveau(),
                'duree'       => $formation->getDuree(),
                'tarif'       => $formation->getTarif(),
                'prerequis'   => $formation->getPrerequis(),
                'description' => $formation->getDescription(),
            ],
        ], );
    }

    #[Route('/{id}/delete', name: 'formation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): Response {
        // Vérification que la formation appartient au centre de l'utilisateur
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette formation.');
        }

        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
            $em->remove($formation);
            $em->flush();
            $this->addFlash('success', 'Formation supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('formation_index');
    }
    #[Route('/api/{id}', name: 'formation_api_detail', methods: ['GET'])]
    public function apiDetail(Formation $formation): JsonResponse {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette formation.');
        }

        return $this->json([
            'id'          => $formation->getId(),
            'title'       => $formation->getTitre(),
            'description' => $formation->getDescription(),
        ]);
    }
}
