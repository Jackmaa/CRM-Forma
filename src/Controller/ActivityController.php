<?php
namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contrôleur pour les activités récentes (API).
 */
class ActivityController extends AbstractController {
    #[Route('/recent-activities', name: 'api_recent_activities', methods: ['GET'])]
    /**
     * Retourne les 10 dernières activités au format JSON.
     *
     * @param Request $request La requête HTTP.
     * @param ActivityRepository $repo Le repository des activités.
     * @param SerializerInterface $serializer Le service de sérialisation.
     * @return JsonResponse La réponse JSON contenant les activités récentes.
     */
    public function recent(
        Request $request,
        ActivityRepository $repo,
        SerializerInterface $serializer
    ): JsonResponse {
        // limit param with guard (1..50), default 5
        $limit = (int) $request->query->get('limit', 5);
        $limit = max(1, min($limit, 50));

        $activities = $repo->findRecent($limit);

        // garantir un format ISO 8601 pour createdAt
        $json = $serializer->serialize(
            $activities,
            'json',
            [
                'groups'          => 'activity:read',
                'datetime_format' => \DateTimeInterface::ATOM,
            ]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
