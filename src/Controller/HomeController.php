<?php
namespace App\Controller;

use App\Repository\CentreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController {
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    /**
     * Affiche la page d'accueil et gère la sélection du centre.
     *
     * @param CentreRepository $centreRepo Le repository des centres.
     * @param Request $request La requête HTTP.
     * @param SessionInterface $session La session utilisateur.
     * @return Response La réponse HTTP avec le rendu de la vue appropriée.
     */
    public function index(
        CentreRepository $centreRepo,
        Request $request,
        SessionInterface $session
    ): Response {
        $count = $centreRepo->count([]);

        // 1) Aucun centre → on redirige vers la création d'un centre
        if ($count === 0) {
            return $this->redirectToRoute('centre_new');
        }

        // 2) Un seul centre → on le mémorise et on va au tableau de bord
        $centres = $centreRepo->findAll();
        if ($count === 1) {
            $session->set('centre_id', $centres[0]->getId());
            return $this->render('home/dashboard.html.twig');
        }

        // 3) Plusieurs centres → on propose une sélection à l'utilisateur
        if ($request->isMethod('POST')) {
            $selectedId = $request->request->getInt('centre_id');
            // (Optionnel : valider que $selectedId est bien dans la liste)
            $session->set('centre_id', $selectedId);
            return $this->render('home/dashboard.html.twig');
        }

        // Affiche la page de sélection de centre si plusieurs centres existent
        return $this->render('centre/select.html.twig', [
            'centres' => $centres,
        ]);
    }
}
