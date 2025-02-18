<?php

namespace App\EventListener;

use App\Entity\Product;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

class ProductLoggerListener
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
         $this->logger = $logger;
    }
    
    /**
     * Logs when a product is created.
     */
     public function postPersist(LifecycleEventArgs $event): void
     {
     $entity = $event->getObject();
     if (!$entity instanceof Product) {
          return;
     }
     
     $this->logger->info('Product created: ' . $entity->getName() . ' (ID: ' . $entity->getId() . ')');
     }

    
    /**
     * Logs when a product is deleted.
     */
    public function postRemove(LifecycleEventArgs $event): void
     {
     $entity = $event->getObject();
     if (!$entity instanceof Product) {
          return;
     }
     
     $this->logger->info('Product deleted: ' . $entity->getName() . ' (ID: ' . $entity->getId() . ')');
     }

}
