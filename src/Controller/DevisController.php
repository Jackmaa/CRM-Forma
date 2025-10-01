<?php
namespace App\Controller;

use App\Entity\Document;
use App\Form\DevisType;
use App\Message\SendDocumentEmail;
use App\Service\DevisSnapshotBuilder;
use App\Service\DocumentNumberGenerator;
use App\Service\DocumentPdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/devis')]
class DevisController extends AbstractController {
    #[Route('/new', name: 'devis_new')]
    public function new (
        Request $r,
        EntityManagerInterface $em,
        DevisSnapshotBuilder $builder,
        DocumentNumberGenerator $num,
        DocumentPdfGenerator $pdf
    ): Response {
        // 1 ligne pré-remplie
        $initialData = [
            'lines' => [
                ['description' => '', 'qty' => 1, 'unit_ht' => 0, 'tva_rate' => 20],
            ],
        ];

        $form = $this->createForm(DevisType::class, $initialData);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $number = $num->nextForDevis();

                                                                                    // Récupère ton centre (adapte selon ton modèle)
            $centre = $em->getRepository(\App\Entity\Centre::class)->findOneBy([]); // TODO: filtrer par user si multi-centre

            $of = [
                'name'    => $centre?->getNom() ?? 'Mon Organisme',
                'siret'   => $centre?->getSiret() ?? '00000000000000',
                'address' => method_exists($centre, 'getAdresseComplete')
                    ? $centre->getAdresseComplete()
                    : ($centre?->getAdresse() ?? 'Adresse'),
                'logo'    => $centre?->getLogoUrl() ?? null,
            ];

            $d    = $form->getData();
            $dest = [
                'company'          => $d['dest_company'],
                'contact_fullname' => $d['dest_contact_fullname'],
                'contact_email'    => $d['dest_contact_email'],
                'address'          => $d['dest_address'] ?? '',
            ];

            // 1) Récupération brute
            $rawLines = $d['lines'] ?? [];

// 2) Filtre & normalisation robuste (gère virgules FR, champs vides, etc.)
            $norm = static function ($v): float {
                // "85,50" -> "85.50" ; "  85 " -> "85"
                if (is_string($v)) {$v = str_replace([' ', ' '], '', $v);
                    $v                            = str_replace(',', '.', $v);}
                return (float) $v;
            };

            $lines = [];
            foreach ($rawLines as $l) {
                $desc = trim((string) ($l['description'] ?? ''));
                if ($desc === '') {continue;} // on zappe les lignes vides

                $qty = $norm($l['qty'] ?? 0);
                $pu  = $norm($l['unit_ht'] ?? 0);
                $tva = $norm($l['tva_rate'] ?? 20);

                $lines[] = [
                    'description' => $desc,
                    'qty'         => $qty,
                    'unit_ht'     => $pu,
                    'tva_rate'    => $tva,
                ];
            }

// Exonération TVA : on impose 0 pour toutes les lignes
            if (! empty($d['tva_exempt'])) {
                foreach ($lines as &$l) {$l['tva_rate'] = 0.0;}
            }

// 3) Meta
            $meta = [
                'formation_title' => $d['formation_title'] ?? null,
                'session_code'    => $d['session_code'] ?? null,
                'hours_total'     => $d['hours_total'] ?? null,
                'modality'        => $d['modality'] ?? null,
                'tva_exempt'      => (bool) ($d['tva_exempt'] ?? false),
                'validity_days'   => (int) ($d['validity_days'] ?? 30),
            ];

// 4) Build snapshot AVEC les lignes normalisées
            $snapshot = $builder->build(['of' => $of, 'dest' => $dest, 'lines' => $lines, 'meta' => $meta]);
            $doc      = new Document();
            $doc->setType(Document::TYPE_DEVIS);
            $doc->setNumber($number);
            $doc->setCentre($centre);
            $doc->setSnapshot($snapshot);

            // arbo : var/storage/docs/{centreId}/DEVIS/{YYYY}/{number}.pdf
            $relPath = sprintf('%d/DEVIS/%s/%s.pdf', $centre?->getId() ?? 0, date('Y'), $number);

            $absPath = $pdf->renderToPdf('docs/devis.html.twig', [
                'document' => $doc,
                'snapshot' => $snapshot,
            ], $relPath);

            $doc->setFilePath($absPath);
            $em->persist($doc);
            $em->flush();

            $this->addFlash('success', "Devis $number généré.");
            return $this->redirectToRoute('devis_show', ['id' => $doc->getId()]);
        }

        return $this->render('devis/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'devis_show')]
    public function show(Document $doc): Response {
        return $this->render('devis/show.html.twig', [
            'doc'      => $doc,
            'snapshot' => $doc->getSnapshot(),
        ]);
    }

    #[Route('/{id}/download', name: 'devis_download')]
    public function download(Document $doc): Response {
        if (! $doc->getFilePath() || ! is_file($doc->getFilePath())) {
            throw $this->createNotFoundException('PDF introuvable.');
        }
        return $this->file($doc->getFilePath(), sprintf('%s.pdf', $doc->getNumber()), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{id}/send', name: 'devis_send', methods: ['POST'])]
    public function send(Document $doc, MessageBusInterface $bus, EntityManagerInterface $em): Response {
        $to = $doc->getSnapshot()['dest']['contact_email'] ?? null;
        if ($to && $doc->getFilePath()) {
            $bus->dispatch(new SendDocumentEmail($doc->getId(), $to));
            $doc->setStatus('SENT');
            $em->flush();
            $this->addFlash('success', 'Devis envoyé (Mailtrap).');
        } else {
            $this->addFlash('error', 'Email destinataire ou PDF manquant.');
        }
        return $this->redirectToRoute('devis_show', ['id' => $doc->getId()]);
    }
}
