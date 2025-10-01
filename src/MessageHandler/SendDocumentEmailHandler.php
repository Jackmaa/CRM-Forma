<?php
namespace App\MessageHandler;

use App\Message\SendDocumentEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

#[AsMessageHandler]
final class SendDocumentEmailHandler {
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private Twig $twig,
        private UrlGeneratorInterface $urlGenerator,
        private string $defaultFrom = 'noreply@crm-forma.local',
        private string $defaultFromName = 'CRM Forma'
    ) {}

    public function __invoke(SendDocumentEmail $m): void {
        $doc = $this->em->getRepository(\App\Entity\Document::class)->find($m->documentId);
        if (! $doc || ! $doc->getFilePath()) {
            return;
        }

        $snapshot = $doc->getSnapshot();

        $fromAddr = $snapshot['of']['email'] ?? $this->defaultFrom;
        $fromName = $snapshot['of']['name'] ?? $this->defaultFromName;

        $downloadUrl = $this->urlGenerator->generate(
            'devis_download',
            ['id' => $doc->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $html = $this->twig->render('emails/devis_ready.html.twig', [
            'document'    => $doc,
            'snapshot'    => $snapshot,
            'downloadUrl' => $downloadUrl,
        ]);

        $email = (new \Symfony\Component\Mime\Email())
            ->from(new Address($fromAddr, $fromName))
            ->to($m->to)
            ->subject(sprintf('Votre devis %s', $doc->getNumber()))
            ->html($html)
            ->attachFromPath(
                $doc->getFilePath(),
                sprintf('%s.pdf', $doc->getNumber()),
                'application/pdf'
            );

        $this->mailer->send($email);
    }
}