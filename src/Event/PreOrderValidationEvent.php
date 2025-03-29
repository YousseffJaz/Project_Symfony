<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PreOrderValidationEvent extends Event
{
    public const NAME = 'order.pre_validation';

    private ?array $originalLineItems = null;

    public function __construct(
        private Order $order,
        private bool $isNewOrder = true,
        ?EntityManagerInterface $entityManager = null,
    ) {
        if (!$isNewOrder && null !== $entityManager) {
            // Charger l'état original de la commande depuis la base de données
            $originalOrder = $entityManager->getUnitOfWork()->getOriginalEntityData($order);
            if ($originalOrder) {
                $this->originalLineItems = [];
                foreach ($order->getLineItems() as $lineItem) {
                    $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($lineItem);
                    if ($originalData) {
                        $this->originalLineItems[$lineItem->getId()] = [
                            'quantity' => $originalData['quantity'] ?? 0,
                            'variant' => $originalData['variant'] ?? null,
                            'stock' => $originalData['stock'] ?? null,
                        ];
                    }
                }
            }
        }
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function isNewOrder(): bool
    {
        return $this->isNewOrder;
    }

    public function getOriginalLineItems(): ?array
    {
        return $this->originalLineItems;
    }
}
