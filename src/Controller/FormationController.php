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

#[Route('/formation', name: 'formation_')]
#[IsGranted('ROLE_USER')]
class FormationController extends AbstractController {
    /**
     * GET  /formation
     */
    #[Route('', name: 'index', methods: ['GET'])]
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
     * GET  /formation/api
     */
    #[Route('/api', name: 'api', methods: ['GET'])]
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
     * GET|POST  /formation/new
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function new (Request $request, EntityManagerInterface $em): Response {
        $formation = new Formation();
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

    /**
     * GET  /formation/{id}
     */
    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Formation $formation): Response {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    /**
     * GET|PATCH  /formation/{id}/edit
     */
    #[Route('/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function edit(
        Request $request,
        Formation $formation,
        EntityManagerInterface $em
    ): Response | JsonResponse {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($request->isMethod('GET')) {
            return $this->render('formation/edit.html.twig', [
                'formation' => $formation,
            ]);
        }

        $data = json_decode($request->getContent(), true);
        isset($data['titre']) && $formation->setTitre($data['titre']);
        isset($data['slug']) && $formation->setSlug($data['slug']);
        isset($data['thematique']) && $formation->setThematique($data['thematique']);
        isset($data['niveau']) && $formation->setNiveau($data['niveau']);
        isset($data['duree']) && $formation->setDuree((int) $data['duree']);
        isset($data['tarif']) && $formation->setTarif($data['tarif']);
        isset($data['prerequis']) && $formation->setPrerequis($data['prerequis']);
        isset($data['description']) && $formation->setDescription($data['description']);
        isset($data['modalites']) && $formation->setModalites($data['modalites']);
        isset($data['objectifs']) && $formation->setObjectifs($data['objectifs']);

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
            ],
        ]);
    }

    /**
     * POST  /formation/{id}/delete
     */
    #[Route('/{id<\d+>}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN_CENTRE')]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): Response {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
            $em->remove($formation);
            $em->flush();
            $this->addFlash('success', 'Formation supprimée.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('formation_index');
    }

    /**
     * GET  /formation/api/{id}
     */
    #[Route('/api/{id<\d+>}', name: 'api_detail', methods: ['GET'])]
    public function apiDetail(Formation $formation): JsonResponse {
        if ($formation->getCentre() !== $this->getUser()->getCentre()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        return $this->json([
            'id'          => $formation->getId(),
            'title'       => $formation->getTitre(),
            'description' => $formation->getDescription(),
        ]);
    }
}
