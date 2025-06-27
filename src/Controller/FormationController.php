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
#[IsGranted('ROLE_ADMIN_CENTRE')]
class FormationController extends AbstractController {
    #[Route('/', name: 'formation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response {
        $repo       = $em->getRepository(Formation::class);
        $formations = $repo->findBy(['centre' => $this->getUser()->getCentre()]);

        return $this->render('formation/formation.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/new', name: 'formation_new', methods: ['GET', 'POST'])]
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
    /**
     * @Route("/api/formations", name="formation_api", methods={"GET"})
     */
    #[Route('/api', name: 'formation_api', methods: ['GET'])]
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
}
