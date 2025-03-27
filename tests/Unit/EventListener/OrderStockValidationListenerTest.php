<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\LineItem;
use App\Entity\StockList;
use App\Entity\Variant;
use App\Event\PreOrderValidationEvent;
use App\EventListener\OrderStockValidationListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderStockValidationListenerTest extends TestCase
{
    private OrderStockValidationListener $listener;
    private Order $order;
    private Product $product;
    private StockList $stockList;
    private Variant $variant;

    protected function setUp(): void
    {
        $this->listener = new OrderStockValidationListener();
        $this->order = new Order();
        $this->product = new Product();
        $this->stockList = new StockList();
        $this->stockList->setProduct($this->product);
        $this->variant = new Variant();
        $this->variant->setProduct($this->product);
        $this->variant->setTitle('Test Variant');
    }

    public function testThrowsExceptionWhenOrderQuantityExceedsStock(): void
    {
        // Arrange
        $this->stockList->setQuantity(5);
        
        $lineItem = new LineItem();
        $lineItem->setProduct($this->product);
        $lineItem->setStock($this->stockList);
        $lineItem->setQuantity(10);
        $lineItem->setVariant($this->variant);
        
        $this->order->addLineItem($lineItem);
        
        $event = new PreOrderValidationEvent($this->order, true);

        // Assert
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('La quantité demandée (10) pour le produit "Test Variant" excède le stock disponible (5)');

        // Act
        $this->listener->onPreOrderValidation($event);
    }

    public function testAllowsOrderWhenStockIsSufficient(): void
    {
        // Arrange
        $this->stockList->setQuantity(10);
        
        $lineItem = new LineItem();
        $lineItem->setProduct($this->product);
        $lineItem->setStock($this->stockList);
        $lineItem->setQuantity(5);
        $lineItem->setVariant($this->variant);
        
        $this->order->addLineItem($lineItem);
        
        $event = new PreOrderValidationEvent($this->order, true);

        // Act & Assert
        try {
            $this->listener->onPreOrderValidation($event);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Une exception n\'aurait pas dû être levée');
        }
    }
} 