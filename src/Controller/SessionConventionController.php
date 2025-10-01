<?php
namespace App\Controller;

use App\Message\GenerateSessionConventions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sessions')]
class SessionConventionController extends AbstractController {
    #[Route('/{id}/conventions/generate', name: 'session_conventions_generate', methods: ['GET', 'POST'])]
    public function generate(int $id, Request $r, MessageBusInterface $bus): Response {
        // si POST + _token présent, tu peux garder la vérif CSRF ici; sinon on tolère GET pour l'appel AJAX depuis Vue
        $bus->dispatch(new GenerateSessionConventions($id));

        if ($r->isXmlHttpRequest()) {
            return $this->json(['ok' => true, 'message' => 'Génération lancée.']);
        }

        $this->addFlash('success', 'Génération & envoi des conventions lancé.');
        return $this->redirectToRoute('session_show', ['id' => $id]);
    }
}