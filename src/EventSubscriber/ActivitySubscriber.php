<?php
namespace App\EventSubscriber;

use App\Entity\Activity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ActivitySubscriber implements EventSubscriber {
    private LoggerInterface $logger;
    private Security $security;
    private array $toRecord = [];

    public function __construct(Security $security, LoggerInterface $logger) {
        $this->security = $security;
        $this->logger   = $logger;
    }
    public function getSubscribedEvents(): array {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
            Events::postFlush,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void {
        $this->logger->info('ActivitySubscriber postPersist for ' . get_class($args->getObject()));
        $this->collect('create', $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void {
        $this->logger->info('ActivitySubscriber postUpdate for ' . get_class($args->getObject()));
        $this->collect('update', $args);
    }

    public function preRemove(LifecycleEventArgs $args): void {
        $this->logger->info('ActivitySubscriber postRemove for ' . get_class($args->getObject()));
        $this->collect('delete', $args);
    }

    private function collect(string $action, LifecycleEventArgs $args): void {
        $entity = $args->getObject();
        // N’enregistre pas les Activity elles-mêmes
        if ($entity instanceof Activity) {
            return;
        }

        $em   = $args->getObjectManager();
        $meta = $em->getClassMetadata(\get_class($entity));
        $ids  = $meta->getIdentifierValues($entity);
        $id   = (int) array_shift($ids);

        $activity = new Activity();
        $activity->setAction($action);
        $activity->setEntityName($meta->getReflectionClass()->getShortName());
        $activity->setEntityId($id);

        $user     = $this->security->getUser();
        $username = $user?->getUserIdentifier() ?: 'Anonyme';
        $activity->setDescription(sprintf(
            '%s %s %s #%d',
            $username,
            match ($action) {
                'create' => 'a créé',
                'update' => 'a modifié',
                'delete' => 'a supprimé',
            },
            $meta->getReflectionClass()->getShortName(),
            $id
        ));
        $activity->setUser($user);

        $this->toRecord[] = $activity;
    }

    public function postFlush(PostFlushEventArgs $args): void {
        $this->logger->info('ActivitySubscriber postFlush, toRecord count = ' . count($this->toRecord));
        if (empty($this->toRecord)) {
            return;
        }
        $em = $args->getObjectManager();
        foreach ($this->toRecord as $activity) {
            $em->persist($activity);
            $this->logger->info('Persisting Activity: ' . $activity->getDescription());
        }
        $this->toRecord = [];
        $em->flush();
    }
}
