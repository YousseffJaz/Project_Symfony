<?php

namespace App\Tests\Service\Order;

use PHPUnit\Framework\TestCase;
use App\Service\Order\OrderService;
use App\Entity\Order;
use App\Entity\Admin;
use App\Repository\OrderRepository;
use App\Repository\LineItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends TestCase
{
    private OrderService $orderService;
    private MockObject $orderRepository;
    private MockObject $lineItemRepository;
    private MockObject $entityManager;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->lineItemRepository = $this->createMock(LineItemRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->lineItemRepository,
            $this->entityManager
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

        $this->lineItemRepository
            ->expects($this->once())
            ->method('totalAmountByStartAndEnd')
            ->with('2024-01-01', '2024-01-31')
            ->willReturn([['total' => 300]]);

        $result = $this->orderService->getTotalsByDateRange($start, $end);

        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('alreadyPaid', $result);
        $this->assertEquals(300, $result['total']);
        $this->assertEquals(150, $result['alreadyPaid']);
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