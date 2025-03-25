<?php

namespace App\Tests\Service\Order;

use PHPUnit\Framework\TestCase;
use App\Service\Order\OrderService;
use App\Entity\Order;
use App\Entity\Admin;
use App\Repository\OrderRepository;
use App\Repository\LineItemRepository;
use App\Repository\StockListRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends TestCase
{
    private OrderService $orderService;
    private MockObject $orderRepository;
    private MockObject $lineItemRepository;
    private MockObject $entityManager;
    private MockObject $stockListRepository;
    private MockObject $transactionRepository;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->lineItemRepository = $this->createMock(LineItemRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stockListRepository = $this->createMock(StockListRepository::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);

        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->lineItemRepository,
            $this->entityManager,
            $this->stockListRepository,
            $this->transactionRepository
        );
    }

    public function testGetOrdersByDateRange(): void
    {
        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-31');

        $mockOrders = [
            $this->createMockOrder(100, 50),
            $this->createMockOrder(200, 100)
        ];

        $this->orderRepository
            ->expects($this->once())
            ->method('findByStartAndEnd')
            ->with('2024-01-01', '2024-01-31')
            ->willReturn($mockOrders);

        $result = $this->orderService->getOrdersByDateRange($start, $end);

        $this->assertCount(2, $result);
        $this->assertSame($mockOrders, $result);
    }

    public function testGetTotalsByDateRange(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');

        $order1 = $this->createMock(Order::class);
        $order1->method('getTotal')->willReturn(500.0);
        $order1->method('getPaid')->willReturn(200.0);

        $order2 = $this->createMock(Order::class);
        $order2->method('getTotal')->willReturn(500.0);
        $order2->method('getPaid')->willReturn(300.0);

        $this->orderRepository
            ->expects($this->once())
            ->method('findByStartAndEnd')
            ->with('2024-01-01', '2024-12-31')
            ->willReturn([$order1, $order2]);

        $result = $this->orderService->getTotalsByDateRange($startDate, $endDate);

        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('alreadyPaid', $result);
        $this->assertEquals(1000.0, $result['total']);
        $this->assertEquals(500.0, $result['alreadyPaid']);
    }

    public function testGetOrdersByStatus(): void
    {
        $mockOrders = [
            $this->createMockOrder(100, 50),
            $this->createMockOrder(200, 100)
        ];

        $this->orderRepository
            ->expects($this->once())
            ->method('findByStatus')
            ->with('pending')
            ->willReturn($mockOrders);

        $result = $this->orderService->getOrdersByStatus('pending');

        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('alreadyPaid', $result);
        $this->assertEquals(300, $result['total']);
        $this->assertEquals(150, $result['alreadyPaid']);
    }

    public function testGetWaitingOrders(): void
    {
        $mockOrders = [
            $this->createMockOrder(100, 50),
            $this->createMockOrder(200, 100)
        ];

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrderNotComplete')
            ->willReturn($mockOrders);

        $result = $this->orderService->getWaitingOrders();

        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('alreadyPaid', $result);
        $this->assertEquals(300, $result['total']);
        $this->assertEquals(150, $result['alreadyPaid']);
    }

    private function createMockOrder(float $total, float $paid): Order
    {
        $order = $this->createMock(Order::class);
        $order->method('getTotal')->willReturn($total);
        $order->method('getPaid')->willReturn($paid);
        return $order;
    }
} 