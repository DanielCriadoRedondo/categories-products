<?php

namespace App\EventListener;

use App\Entity\Product;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

class ProductEventListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $product = $event->getObject();

        // Verifica si la entidad es una instancia de Product
        if (!$product instanceof Product) {
            return;
        }

        $this->logger->info('Product Created', [
            'id' => $product->getId(), // Ahora sí tendrá el ID
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $product = $event->getObject();

        // Verifica si la entidad es una instancia de Product
        if (!$product instanceof Product) {
            return;
        }

        $this->logger->info('Product Deleted', [
            'id' => $product->getId(),
            'name' => $product->getName(),
        ]);
    }
}

