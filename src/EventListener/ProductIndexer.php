<?php

namespace App\EventListener;

use App\Entity\Product;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

class ProductIndexer
{
    private ObjectPersisterInterface $persister;

    public function __construct(ObjectPersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->persister->insertOne($entity);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->persister->replaceOne($entity);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->persister->deleteOne($entity);
    }
} 