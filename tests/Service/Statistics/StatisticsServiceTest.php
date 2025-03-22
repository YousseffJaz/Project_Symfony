<?php

namespace App\Tests\Service\Statistics;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Service\Statistics\StatisticsService;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Entity\Order;
use App\Entity\Product;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Enum\OrderStatus;

class StatisticsServiceTest extends TestCase
{
    private StatisticsService $statisticsService;
    private MockObject $orderRepository;
    private MockObject $productRepository;
    private MockObject $stockListRepository;
    private MockObject $priceListRepository;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->stockListRepository = $this->createMock(StockListRepository::class);
        $this->priceListRepository = $this->createMock(PriceListRepository::class);

        $this->statisticsService = new StatisticsService(
            $this->orderRepository,
            $this->productRepository,
            $this->stockListRepository,
            $this->priceListRepository
        );
    }

    public function testCalculatePaymentStats(): void
    {
        // Création des commandes simulées
        $order1 = $this->createMock(Order::class);
        $order1->method('getTotal')->willReturn(100.0);
        
        $order2 = $this->createMock(Order::class);
        $order2->method('getTotal')->willReturn(200.0);

        // Configuration des retours du repository pour différentes méthodes de paiement
        $this->orderRepository
            ->method('findByPaymentMethod')
            ->willReturnCallback(function($method) use ($order1, $order2) {
                return match($method) {
                    PaymentMethod::CARD->value => [$order1, $order2],
                    PaymentMethod::CASH->value => [$order1],
                    default => []
                };
            });

        // Configuration des retours du repository pour différents types de paiement
        $this->orderRepository
            ->method('findByPaymentType')
            ->willReturnCallback(function($type) use ($order1, $order2) {
                return match($type) {
                    PaymentType::LOCAL->value => [$order1, $order2],
                    default => []
                };
            });

        // Configuration des retours du repository pour différents statuts
        $this->orderRepository
            ->method('findByStatus')
            ->willReturnCallback(function($status) use ($order1, $order2) {
                return match($status) {
                    OrderStatus::WAITING->value => [$order1],
                    OrderStatus::PAID->value => [$order2],
                    default => []
                };
            });

        $result = $this->statisticsService->calculatePaymentStats();

        // Vérification des résultats
        $this->assertArrayHasKey('methods', $result);
        $this->assertArrayHasKey('types', $result);
        $this->assertArrayHasKey('status', $result);

        // Vérification des montants par méthode de paiement
        $this->assertEquals(300.0, $result['methods'][PaymentMethod::CARD->value]);
        $this->assertEquals(100.0, $result['methods'][PaymentMethod::CASH->value]);
        $this->assertEquals(0.0, $result['methods'][PaymentMethod::CHECK->value]);

        // Vérification des montants par type de paiement
        $this->assertEquals(300.0, $result['types'][PaymentType::LOCAL->value]);
        $this->assertEquals(0.0, $result['types'][PaymentType::ONLINE->value]);

        // Vérification des montants par statut
        $this->assertEquals(100.0, $result['status'][OrderStatus::WAITING->value]);
        $this->assertEquals(200.0, $result['status'][OrderStatus::PAID->value]);
    }

    public function testCalculateStockValue(): void
    {
        // Création des produits simulés
        $product1 = $this->createMock(Product::class);
        $product2 = $this->createMock(Product::class);

        // Configuration du ProductRepository
        $this->productRepository
            ->method('findBy')
            ->with(['archive' => false, 'digital' => false])
            ->willReturn([$product1, $product2]);

        // Configuration du StockListRepository
        $this->stockListRepository
            ->method('findStockName')
            ->willReturn(['stock1', 'stock2']);

        // Configuration des quantités en stock
        $this->stockListRepository
            ->method('findQuantityByProductAndStock')
            ->willReturnMap([
                [$product1, 'stock1', [['quantity' => 5]]],
                [$product1, 'stock2', [['quantity' => 3]]],
                [$product2, 'stock1', [['quantity' => 2]]],
                [$product2, 'stock2', [['quantity' => 4]]]
            ]);

        // Configuration des prix
        $this->priceListRepository
            ->method('findByProduct')
            ->willReturnMap([
                [$product1, [['price' => 100.0], ['price' => 120.0]]],
                [$product2, [['price' => 200.0], ['price' => 220.0]]]
            ]);

        $stockValue = $this->statisticsService->calculateStockValue();

        // La valeur attendue est calculée comme suit :
        // Product1: ((100 + 120) / 2) * (5 + 3) = 110 * 8 = 880
        // Product2: ((200 + 220) / 2) * (2 + 4) = 210 * 6 = 1260
        // Total: 880 + 1260 = 2140
        $this->assertEquals(2140.0, $stockValue);
    }

    public function testGetBestSellers(): void
    {
        // Configuration des meilleures ventes
        $this->orderRepository
            ->method('findBestProducts')
            ->willReturn([
                ['product' => 'Product 1', 'total' => 1000],
                ['product' => 'Product 2', 'total' => 500]
            ]);

        $this->orderRepository
            ->method('findBestCategories')
            ->willReturn([
                ['category' => 'Category 1', 'total' => 2000],
                ['category' => 'Category 2', 'total' => 1500]
            ]);

        $this->orderRepository
            ->method('findBestCustomers')
            ->willReturn([
                ['customer' => 'Customer 1', 'total' => 3000],
                ['customer' => 'Customer 2', 'total' => 2500]
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