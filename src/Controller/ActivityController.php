<?php
namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contrôleur pour les activités récentes (API).
 */
class ActivityController extends AbstractController {
    #[Route('/api/recent-activities', name: 'api_recent_activities', methods: ['GET'])]
    /**
     * Retourne les 10 dernières activités au format JSON.
     *
     * @param ActivityRepository $repo Le repository des activités.
     * @param SerializerInterface $serializer Le service de sérialisation.
     * @return JsonResponse La réponse JSON contenant les activités récentes.
     */
    public function recent(ActivityRepository $repo, SerializerInterface $serializer): JsonResponse {
        $activities = $repo->findRecent(10);
        $json       = $serializer->serialize($activities, 'json', ['groups' => 'activity:read']);

        return new JsonResponse($json, 200, [], true);
    }
}
