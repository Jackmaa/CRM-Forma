<?php
namespace App\Controller;

use App\Repository\CentreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController {
    #[Route('/', name: 'home')]
    public function index(
        CentreRepository $centreRepo,
        Request $request,
        SessionInterface $session
    ): Response {
        $count = $centreRepo->count([]);

        // 1) Pas de centre → on crée
        if ($count === 0) {
            return $this->redirectToRoute('centre_new');
        }

        // 2) Un seul centre → on le mémorise et on va au login
        $centres = $centreRepo->findAll();
        if ($count === 1) {
            $session->set('centre_id', $centres[0]->getId());
            return $this->render('home/dashboard.html.twig');
        }

        // 3) Plusieurs centres → on propose une sélection
        if ($request->isMethod('POST')) {
            $selectedId = $request->request->getInt('centre_id');
            // (Optionnel : valider que $selectedId est bien dans la liste)
            $session->set('centre_id', $selectedId);
            return $this->render('home/dashboard.html.twig');
        }

        return $this->render('centre/select.html.twig', [
            'centres' => $centres,
        ]);
    }
}
