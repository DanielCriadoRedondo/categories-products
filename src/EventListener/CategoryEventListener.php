<?php

namespace App\EventListener;

use App\Entity\Category;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

class CategoryEventListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $category = $event->getObject();

        // Verifica si la entidad es una instancia de Category
        if (!$category instanceof Category) {
            return;
        }

        $this->logger->info('Category Created', [
            'id' => $category->getId(), // Ahora sí tendrá el ID
            'title' => $category->getTitle(),
            'details' => $category->getDetails(),
        ]);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $category = $event->getObject();

        // Verifica si la entidad es una instancia de Category
        if (!$category instanceof Category) {
            return;
        }

        $this->logger->info('Category Deleted', [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
        ]);
    }
}
