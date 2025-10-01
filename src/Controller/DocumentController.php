<?php
namespace App\Controller;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/documents')]
class DocumentController extends AbstractController {
    #[Route('/{id}/download', name: 'document_download')]
    public function download(Document $doc): Response {
        if (! $doc->getFilePath() || ! is_file($doc->getFilePath())) {
            throw $this->createNotFoundException('PDF introuvable.');
        }
        return $this->file(
            $doc->getFilePath(),
            sprintf('%s.pdf', $doc->getNumber()),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }
}
