<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\StockList;
use App\Entity\Variant;
use App\Event\PreOrderValidationEvent;
use App\EventSubscriber\OrderStockValidationSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderStockValidationSubscriberTest extends TestCase
{
    private OrderStockValidationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriber = new OrderStockValidationSubscriber();
    }

    public function testGetSubscribedEvents(): void
    {
        $events = OrderStockValidationSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(PreOrderValidationEvent::NAME, $events);
        $this->assertIsArray($events[PreOrderValidationEvent::NAME]);
        $this->assertContains(['validateStock', 20], $events[PreOrderValidationEvent::NAME]);
        $this->assertContains(['validateMinimumOrderAmount', 10], $events[PreOrderValidationEvent::NAME]);
    }

    public function testValidateStockWithNoVariant(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Un produit de la commande n\'a pas de variant associé.');

        $order = new Order();
        $lineItem = new LineItem();
        $order->addLineItem($lineItem);

        $event = new PreOrderValidationEvent($order);
        $this->subscriber->validateStock($event);
    }

    public function testValidateStockWithNoStock(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Aucun stock n\'est associé au produit');

        $order = new Order();
        $lineItem = new LineItem();
        $variant = new Variant();
        $variant->setTitle('Test Variant');
        $lineItem->setVariant($variant);
        $order->addLineItem($lineItem);

        $event = new PreOrderValidationEvent($order);
        $this->subscriber->validateStock($event);
    }

    public function testValidateStockWithInsufficientStock(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('La quantité demandée (10) pour le produit "Test Variant" excède le stock disponible (5)');

        $order = new Order();
        $lineItem = new LineItem();
        $variant = new Variant();
        $variant->setTitle('Test Variant');
        $stockList = new StockList();
        $stockList->setQuantity(5);
        $lineItem->setVariant($variant);
        $lineItem->setStock($stockList);
        $lineItem->setQuantity(10);
        $order->addLineItem($lineItem);

        $event = new PreOrderValidationEvent($order);
        $this->subscriber->validateStock($event);
    }

    public function testValidateMinimumOrderAmount(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Le montant total de la commande');

        $order = new Order();
        $lineItem = new LineItem();
        $lineItem->setPrice(5.0);
        $order->addLineItem($lineItem);

        $event = new PreOrderValidationEvent($order);
        $this->subscriber->validateMinimumOrderAmount($event);
    }
} 