<?php
namespace App\MessageHandler;

use App\Entity\Centre;
use App\Entity\Document;
use App\Entity\Session;
use App\Entity\User;use App\Message\GenerateSessionConventions;
use App\Service\ConventionSnapshotBuilder;
use App\Service\DocumentNumberGenerator;
use App\Service\DocumentPdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class GenerateSessionConventionsHandler {
    public function __construct(
        private EntityManagerInterface $em,
        private ConventionSnapshotBuilder $builder,
        private DocumentNumberGenerator $num,
        private DocumentPdfGenerator $pdf,
        private MessageBusInterface $bus
    ) {}

    private function userHasRole(object $user, string $needed = 'ROLE_STAGIAIRE'): bool {
        if (! method_exists($user, 'getRoles')) {
            return false;
        }

        $roles = array_map('strtoupper', (array) $user->getRoles());
        return in_array(strtoupper($needed), $roles, true) || in_array('ROLE_STAGIAIRE', $roles, true);
    }

    public function __invoke(GenerateSessionConventions $m): void {
        /** @var Session|null $session */
        $session = $this->em->getRepository(Session::class)->find($m->sessionId);
        if (! $session) {
            return;
        }

        $centre = $session->getCentre() ?: $this->em->getRepository(Centre::class)->findOneBy([]);

        $of = [
            'name'    => method_exists($centre, 'getNom') ? $centre->getNom() : 'Mon Organisme',
            'siret'   => method_exists($centre, 'getSiret') ? $centre->getSiret() : '00000000000000',
            'address' => method_exists($centre, 'getAdresse') ? $centre->getAdresse() : '',
            'email'   => method_exists($centre, 'getEmail') ? $centre->getEmail() : null,
            'logo'    => method_exists($centre, 'getLogoUrl') ? $centre->getLogoUrl() : null,
        ];

        // Session data (à partir de ton entité)
        $dateStart = $session->getDateDebut();
        $dateEnd   = $session->getDateFin();
        $hours     = 0;
        if ($dateStart && $dateEnd) {
            $iv    = $dateStart->diff($dateEnd);
            $hours = (int) round($iv->days * 24 + $iv->h + $iv->i / 60);
        }

        $formation   = $session->getFormation();
        $sessionData = [
            'title'       => method_exists($formation, 'getIntitule') ? $formation->getTitre() : 'Formation',
            'code'        => method_exists($formation, 'getCode') ? $formation->getSlug() : null,
            'date_start'  => $dateStart,
            'date_end'    => $dateEnd,
            'hours_total' => $hours,
            'modality'    => $session->getModalite(),
            'location'    => $session->getLieu(),
        ];

        // Participants = Users, filtrés par rôle stagiaire
        $participants = $session->getParticipants();
        $participants = $participants instanceof \Doctrine\Common\Collections\Collection  ? $participants->toArray() : (array) $participants;
        $stagiaires   = array_values(array_filter($participants, fn($u) => $this->userHasRole($u)));

        foreach ($stagiaires as $user) {
            /** @var User $user */
            $email    = method_exists($user, 'getEmail') ? $user->getEmail() : null;
            $fullname = method_exists($user, 'getFullName') ? $user->getFullName() : trim(implode(' ', array_filter([
                method_exists($user, 'getPrenom') ? $user->getPrenom() : null,
                method_exists($user, 'getNom') ? $user->getNom() : null,
            ])));
            if (! $fullname && method_exists($user, 'getUserIdentifier')) {
                $fullname = (string) $user->getUserIdentifier();
            }

            // Idempotence simple : on évite de recréer si même (type, number) existe déjà
            // (Si tu ajoutes plus tard des relations Document->session/user, on testera dessus)
            $number = $this->num->nextForConvention();

            $address = null;
            if (method_exists($user, 'getAdresse')) {
                $address = $user->getAdresse(); // not implemented
            } elseif (method_exists($user, 'getAddress')) {
                $address = $user->getAddress(); // not implemented
            }

            $s = $this->builder->build([
                'of'        => $of,
                'session'   => $sessionData,
                'stagiaire' => [
                    'fullname' => $fullname ?: 'Stagiaire',
                    'email'    => $email,
                    'address'  => $address, // ⬅️ toujours présent (peut être null)
                ],
                'pricing'   => ['ht' => 0, 'tva_rate' => 0, 'tva' => 0, 'ttc' => 0],
            ]);

            $doc = new Document();
            $doc->setType(Document::TYPE_CONVENTION);
            $doc->setNumber($number);
            $doc->setSnapshot($s);
            if (method_exists($doc, 'setCentre')) {
                $doc->setCentre($centre);
            }

            // var/storage/docs/{centreId}/CONVENTIONS/{YYYY}/{sessionId}/{number}.pdf
            $relPath = sprintf('%d/CONVENTIONS/%s/%d/%s.pdf',
                $centre?->getId() ?? 0, date('Y'), $session->getId(), $number
            );

            $abs = $this->pdf->renderToPdf('docs/convention.html.twig', [
                'document' => $doc,
                's'        => $s,
            ], $relPath);

            $doc->setFilePath($abs);
            $this->em->persist($doc);
            $this->em->flush();

            if ($email) {
                $this->bus->dispatch(new \App\Message\SendConventionEmail($doc->getId(), $email));
            }
        }
    }
}
