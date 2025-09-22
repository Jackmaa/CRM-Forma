<?php
// src/EventSubscriber/OnboardingRedirectSubscriber.php
namespace App\EventSubscriber;

use App\Entity\Centre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OnboardingRedirectSubscriber implements EventSubscriberInterface {
    public function __construct(
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $url,
    ) {}

    public function onKernelRequest(RequestEvent $event): void {
        if (! $event->isMainRequest()) {
            return;
        }

        $req  = $event->getRequest();
        $path = $req->getPathInfo();

        // Laisse passer ces chemins pour éviter les boucles
        $exclusions = [
            '/admin/centre/new', '/api', '/login', '/logout',
            '/_profiler', '/_wdt', '/assets', '/build',
        ];
        foreach ($exclusions as $x) {
            if (str_starts_with($path, $x)) {
                return;
            }

        }

        // Si la table n’est pas encore créée (dev sans migrations), on ne fait rien
        try {
            $count = (int) $this->em->getRepository(Centre::class)->count([]);
        } catch (\Throwable $e) {
            return;
        }

        // Si aucun centre → onboarding
        if ($count === 0) {
            $event->setResponse(new RedirectResponse(
                $this->url->generate('centre_new')
            ));
        }
    }

    public static function getSubscribedEvents(): array {
        // Priorité haute pour passer avant d’autres redirections
        return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
    }
}
