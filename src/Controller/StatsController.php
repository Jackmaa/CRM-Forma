<?php
// src/Controller/StatsController.php
namespace App\Controller;

use App\Repository\FormationRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour les statistiques du tableau de bord.
 */
class StatsController extends AbstractController {

    public function __construct(
        private UserRepository $userRepo,
        private FormationRepository $formationRepo,
        private SessionRepository $sessionRepo,
        private Connection $connection
    ) {}

    /**
     * Affiche la page des statistiques (vue Twig).
     *
     * @return Response La vue des statistiques.
     */
    #[Route('/stats', name: 'app_stats')]
    public function index(): Response {
        return $this->render('stats/stats.html.twig');
    }

    /**
     * Retourne les KPIs du dashboard (API JSON).
     *
     * @return JsonResponse Les indicateurs clés (utilisateurs, formations, rapports, sessions).
     */
    #[Route('/api/stats/kpis', name: 'dashboard_kpis', methods: ['GET'])]
    public function kpis(): JsonResponse {
        $centre = $this->getUser()->getCentre();

        $usersCount      = $this->userRepo->count(['centre' => $centre]);
        $formationsCount = $this->formationRepo->count(['centre' => $centre]);

        // Rapports = sessions terminées ce mois-ci (exemple)
        $reportsCount = $this->sessionRepo->countFinishedThisMonth($centre);

        // Sessions actives : dateDebut ≤ now ≤ dateFin
        $sessionsCount = $this->sessionRepo->countActive($centre);

        return $this->json([
            'users'      => $usersCount,
            'formations' => $formationsCount,
            'reports'    => $reportsCount,
            'sessions'   => $sessionsCount,
        ]);
    }
}
