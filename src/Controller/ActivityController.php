<?php
namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ActivityController extends AbstractController {
    #[Route('/api/recent-activities', name: 'api_recent_activities', methods: ['GET'])]
    public function recent(ActivityRepository $repo, SerializerInterface $serializer): JsonResponse {
        $activities = $repo->findRecent(10);
        $json       = $serializer->serialize($activities, 'json', ['groups' => 'activity:read']);

        return new JsonResponse($json, 200, [], true);
    }
}
