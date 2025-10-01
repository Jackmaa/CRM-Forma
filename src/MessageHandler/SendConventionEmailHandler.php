<?php
namespace App\MessageHandler;

use App\Entity\Document;
use App\Message\SendConventionEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

#[AsMessageHandler]
final class SendConventionEmailHandler {
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private Twig $twig,
        private UrlGeneratorInterface $urlGen,
        private string $defaultFrom = 'noreply@crm-forma.local',
        private string $defaultFromName = 'CRM Forma'
    ) {}

    public function __invoke(SendConventionEmail $m): void {
        /** @var Document|null $doc */
        $doc = $this->em->getRepository(Document::class)->find($m->documentId);
        if (! $doc || ! $doc->getFilePath()) {
            return;
        }

        $s        = $doc->getSnapshot();
        $fromAddr = $s['of']['email'] ?? $this->defaultFrom;
        $fromName = $s['of']['name'] ?? $this->defaultFromName;

        $downloadUrl = $this->urlGen->generate('document_download', ['id' => $doc->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $html = $this->twig->render('emails/convention_ready.html.twig', [
            'document'    => $doc,
            's'           => $s,
            'downloadUrl' => $downloadUrl,
        ]);

        $email = (new Email())
            ->from(new Address($fromAddr, $fromName))
            ->to($m->to)
            ->subject(sprintf('Votre convention %s', $doc->getNumber()))
            ->html($html)
            ->attachFromPath($doc->getFilePath(), sprintf('%s.pdf', $doc->getNumber()), 'application/pdf');

        $this->mailer->send($email);
    }
}
