<?php
namespace App\Controller;

use App\Service\DocumentPdfGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DevisSmokeController extends AbstractController {
    #[Route('/devis/smoke', name: 'devis_smoke')]
    public function smoke(DocumentPdfGenerator $pdf): Response {
        // Données minimales pour tester
        $snapshot = [
            'of'     => [
                'name'    => 'Mon Organisme',
                'siret'   => '00000000000000',
                'address' => '1 rue de la Formation, 01000 Bourg-en-Bresse',
                'logo'    => null, // ou une URL publique si tu veux tester l’image
            ],
            'dest'   => [
                'company'          => 'Société Test',
                'contact_fullname' => 'Jean Test',
                'contact_email'    => 'jean@test.com',
                'address'          => '2 avenue du Client, 69000 Lyon',
            ],
            'lines'  => [
                ['description' => 'Module A', 'qty' => 7, 'unit_ht' => 85, 'tva_rate' => 20, 'total_ht' => 595, 'tva_amount' => 119, 'total_ttc' => 714],
                ['description' => 'Module B', 'qty' => 1, 'unit_ht' => 250, 'tva_rate' => 20, 'total_ht' => 250, 'tva_amount' => 50, 'total_ttc' => 300],
            ],
            'meta'   => [
                'formation_title' => 'Formation “Bases Web”',
                'session_code'    => 'S-TEST',
                'hours_total'     => 8,
                'modality'        => 'presentiel',
                'tva_exempt'      => false,
                'validity_days'   => 30,
                'validity_end'    => (new \DateTimeImmutable('+30 days'))->format('Y-m-d'),
            ],
            'totals' => [
                'ht'  => 845,
                'tva' => 169,
                'ttc' => 1014,
            ],
        ];

        $number  = 'DEV-TEST-00001';
        $relPath = sprintf('docs/%d/DEVIS/%s/%s.pdf', 0, date('Y'), $number);

        $absPath = $pdf->renderToPdf('docs/devis.html.twig', [
            'document' => (object) ['number' => $number], // petit stub
            'snapshot' => $snapshot,
        ], $relPath);

        // Sert le PDF dans le navigateur pour vérif instantanée
        return $this->file($absPath, 'devis-test.pdf', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
