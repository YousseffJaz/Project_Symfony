<?php

namespace App\EventSubscriber;

use App\Event\PreOrderValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderStockValidationSubscriber implements EventSubscriberInterface
{
    private const MINIMUM_ORDER_AMOUNT = 10.0; // Montant minimum de commande en euros

    public static function getSubscribedEvents(): array
    {
        return [
            PreOrderValidationEvent::NAME => [
                ['validateStock', 20],
                ['validateMinimumOrderAmount', 10]
            ]
        ];
    }

    public function validateMinimumOrderAmount(PreOrderValidationEvent $event): void
    {
        $order = $event->getOrder();
        $total = $order->getTotal();

        if ($total < self::MINIMUM_ORDER_AMOUNT) {
            throw new BadRequestHttpException(
                sprintf(
                    'Le montant total de la commande (%.2f€) est inférieur au minimum requis (%.2f€)',
                    $total,
                    self::MINIMUM_ORDER_AMOUNT
                )
            );
        }
    }

    public function validateStock(PreOrderValidationEvent $event): void
    {
        $order = $event->getOrder();
        $originalLineItems = $event->getOriginalLineItems();
        
        foreach ($order->getLineItems() as $lineItem) {
            $variant = $lineItem->getVariant();
            if (!$variant) {
                throw new BadRequestHttpException('Un produit de la commande n\'a pas de variant associé.');
            }

            $stock = $lineItem->getStock();
            if (!$stock) {
                throw new BadRequestHttpException(
                    sprintf(
                        'Aucun stock n\'est associé au produit "%s". Veuillez sélectionner un stock.',
                        $variant->getTitle()
                    )
                );
            }

            $requestedQuantity = $lineItem->getQuantity();
            $availableStock = $stock->getQuantity();
            
            // Si c'est une nouvelle commande, on vérifie tout
            if ($event->isNewOrder()) {
                if ($requestedQuantity > $availableStock) {
                    throw new BadRequestHttpException(
                        sprintf(
                            'La quantité demandée (%d) pour le produit "%s" excède le stock disponible (%d)',
                            $requestedQuantity,
                            $variant->getTitle(),
                            $availableStock
                        )
                    );
                }
                continue;
            }
            
            // Pour une modification de commande
            $originalData = $originalLineItems[$lineItem->getId()] ?? null;
            
            // Si c'est un nouveau produit dans la commande
            if ($originalData === null) {
                if ($requestedQuantity > $availableStock) {
                    throw new BadRequestHttpException(
                        sprintf(
                            'La quantité demandée (%d) pour le nouveau produit "%s" excède le stock disponible (%d)',
                            $requestedQuantity,
                            $variant->getTitle(),
                            $availableStock
                        )
                    );
                }
                continue;
            }
            
            // Si la quantité a été augmentée
            $quantityDiff = $requestedQuantity - $originalData['quantity'];
            if ($quantityDiff > 0 && $quantityDiff > $availableStock) {
                throw new BadRequestHttpException(
                    sprintf(
                        'L\'augmentation de quantité (%d) pour le produit "%s" excède le stock disponible (%d)',
                        $quantityDiff,
                        $variant->getTitle(),
                        $availableStock
                    )
                );
            }
        }
    }
} 