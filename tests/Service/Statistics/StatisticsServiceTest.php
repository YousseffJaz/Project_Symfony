<?php

declare(strict_types=1);

namespace App\Tests\Service\Statistics;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\StockList;
use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Repository\OrderRepository;
use App\Repository\StockListRepository;
use App\Service\Cache\StatisticsCacheService;
use App\Service\Statistics\StatisticsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatisticsServiceTest extends TestCase
{
    private StatisticsService $statisticsService;
    private MockObject|OrderRepository $orderRepository;
    private MockObject|StockListRepository $stockListRepository;
    private MockObject|StatisticsCacheService $cacheService;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->stockListRepository = $this->createMock(StockListRepository::class);
        $this->cacheService = $this->createMock(StatisticsCacheService::class);

        $this->statisticsService = new StatisticsService(
            $this->orderRepository,
            $this->stockListRepository,
            $this->cacheService
        );
    }

    public function testCalculatePaymentStats(): void
    {
        // Configuration du cache
        $this->cacheService
            ->method('getPaymentStats')
            ->willReturnCallback(fn (callable $callback) => $callback());

        // Création des commandes simulées
        $order1 = $this->createMock(Order::class);
        $order1->method('getTotal')->willReturn(100.0);
        $order1->method('getPaymentMethod')->willReturn(PaymentMethod::CARD->value);
        $order1->method('getPaymentType')->willReturn(PaymentType::LOCAL->value);
        $order1->method('getStatus')->willReturn(OrderStatus::WAITING->value);

        $order2 = $this->createMock(Order::class);
        $order2->method('getTotal')->willReturn(200.0);
        $order2->method('getPaymentMethod')->willReturn(PaymentMethod::BANK->value);
        $order2->method('getPaymentType')->willReturn(PaymentType::ONLINE->value);
        $order2->method('getStatus')->willReturn(OrderStatus::PAID->value);

        // Configuration du repository
        $this->orderRepository
            ->method('findAll')
            ->willReturn([$order1, $order2]);

        $result = $this->statisticsService->calculatePaymentStats();

        // Vérification des résultats
        $this->assertArrayHasKey('methods', $result);
        $this->assertArrayHasKey('types', $result);
        $this->assertArrayHasKey('status', $result);

        // Vérification des montants par méthode de paiement
        $methodStats = array_fill(0, 8, 0);
        $methodStats[PaymentMethod::CARD->value] = 100.0;
        $methodStats[PaymentMethod::BANK->value] = 200.0;
        $this->assertEquals($methodStats, $result['methods']);

        // Vérification des montants par type de paiement
        $typeStats = array_fill(0, 2, 0);
        $typeStats[PaymentType::LOCAL->value] = 100.0;
        $typeStats[PaymentType::ONLINE->value] = 200.0;
        $this->assertEquals($typeStats, $result['types']);

        // Vérification des montants par statut
        $statusStats = array_fill(0, 4, 0);
        $statusStats[OrderStatus::WAITING->value] = 100.0;
        $statusStats[OrderStatus::PAID->value] = 200.0;
        $this->assertEquals($statusStats, $result['status']);
    }

    public function testCalculateStockValue(): void
    {
        // Configuration du cache
        $this->cacheService
            ->method('getStockValue')
            ->willReturnCallback(fn (callable $callback) => $callback());

        // Création des produits et stocks simulés
        $product1 = $this->createMock(Product::class);
        $product1->method('getPrice')->willReturn(100.0);

        $product2 = $this->createMock(Product::class);
        $product2->method('getPrice')->willReturn(200.0);

        $stock1 = $this->createMock(StockList::class);
        $stock1->method('getQuantity')->willReturn(5);
        $stock1->method('getProduct')->willReturn($product1);

        $stock2 = $this->createMock(StockList::class);
        $stock2->method('getQuantity')->willReturn(3);
        $stock2->method('getProduct')->willReturn($product2);

        // Configuration du repository
        $this->stockListRepository
            ->method('findAll')
            ->willReturn([$stock1, $stock2]);

        $result = $this->statisticsService->calculateStockValue();

        // La valeur attendue est :
        // (100 * 5) + (200 * 3) = 500 + 600 = 1100
        $this->assertEquals(1100.0, $result);
    }

    public function testGetBestSellers(): void
    {
        // Configuration du cache
        $this->cacheService
            ->method('getBestSellers')
            ->willReturnCallback(fn (callable $callback) => $callback());

        // Configuration des meilleures ventes
        $this->orderRepository
            ->method('findBestProducts')
            ->willReturn([
                ['product' => 'Product 1', 'total' => 1000],
                ['product' => 'Product 2', 'total' => 500],
            ]);

        $this->orderRepository
            ->method('findBestCategories')
            ->willReturn([
                ['category' => 'Category 1', 'total' => 2000],
                ['category' => 'Category 2', 'total' => 1500],
            ]);

        $this->orderRepository
            ->method('findBestCustomers')
            ->willReturn([
                ['customer' => 'Customer 1', 'total' => 3000],
                ['customer' => 'Customer 2', 'total' => 2500],
            ]);

        $result = $this->statisticsService->getBestSellers();

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('customers', $result);

        $this->assertCount(2, $result['products']);
        $this->assertCount(2, $result['categories']);
        $this->assertCount(2, $result['customers']);

        $this->assertEquals(1000, $result['products'][0]['total']);
        $this->assertEquals(2000, $result['categories'][0]['total']);
        $this->assertEquals(3000, $result['customers'][0]['total']);
    }
}
