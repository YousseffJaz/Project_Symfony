<?php

declare(strict_types=1);

namespace App\Tests\Service\Cache;

use App\Service\Cache\StatisticsCacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class StatisticsCacheServiceTest extends TestCase
{
    private StatisticsCacheService $statisticsCacheService;
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->statisticsCacheService = new StatisticsCacheService($this->cache);
    }

    public function testGetPaymentStats(): void
    {
        $expectedData = ['payment' => 'stats'];

        // Pré-remplir le cache
        $item = $this->cache->getItem('stats_payment');
        $item->set($expectedData);
        $this->cache->save($item);

        $callback = function () {
            $this->fail('Callback should not be called when data is in cache');
        };

        $result = $this->statisticsCacheService->getPaymentStats($callback);
        $this->assertEquals($expectedData, $result);
    }

    public function testGetPaymentStatsFromCallbackWhenCacheEmpty(): void
    {
        $expectedData = ['payment' => 'fresh'];

        $callback = function () use ($expectedData) {
            return $expectedData;
        };

        $result = $this->statisticsCacheService->getPaymentStats($callback);
        $this->assertEquals($expectedData, $result);

        // Vérifier que les données sont en cache
        $item = $this->cache->getItem('stats_payment');
        $this->assertTrue($item->isHit());
        $this->assertEquals($expectedData, $item->get());
    }

    public function testGetPaymentStatsWithCacheException(): void
    {
        $expectedData = ['payment' => 'fallback'];

        // Simuler une erreur de cache en utilisant un mock
        $mockCache = $this->createMock(ArrayAdapter::class);
        $mockCache->method('getItem')
            ->willThrowException(new \Exception('Cache error'));

        $service = new StatisticsCacheService($mockCache);

        $callback = function () use ($expectedData) {
            return $expectedData;
        };

        $result = $service->getPaymentStats($callback);
        $this->assertEquals($expectedData, $result);
    }

    public function testGetBestSellers(): void
    {
        $expectedData = ['best' => 'sellers'];

        // Pré-remplir le cache
        $item = $this->cache->getItem('stats_best_sellers');
        $item->set($expectedData);
        $this->cache->save($item);

        $callback = function () {
            $this->fail('Callback should not be called when data is in cache');
        };

        $result = $this->statisticsCacheService->getBestSellers($callback);
        $this->assertEquals($expectedData, $result);
    }

    public function testGetTotalAmount(): void
    {
        $expectedData = ['total' => 1500.50];

        // Pré-remplir le cache
        $item = $this->cache->getItem('stats_total_amount');
        $item->set($expectedData);
        $this->cache->save($item);

        $callback = function () {
            $this->fail('Callback should not be called when data is in cache');
        };

        $result = $this->statisticsCacheService->getTotalAmount($callback);
        $this->assertEquals($expectedData, $result);
    }

    public function testClearCache(): void
    {
        // Pré-remplir le cache avec des données
        $items = [
            'stats_payment' => ['payment' => 'data'],
            'stats_stock_value' => 1500.50,
            'stats_best_sellers' => ['best' => 'sellers'],
            'stats_total_amount' => ['total' => 1000],
            'stats_total_orders_count' => 42,
        ];

        foreach ($items as $key => $value) {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $this->cache->save($item);
        }

        $this->statisticsCacheService->clearCache();

        // Vérifier que les éléments ont été supprimés
        foreach (array_keys($items) as $key) {
            $this->assertFalse($this->cache->getItem($key)->isHit());
        }
    }

    public function testClearCacheHandlesException(): void
    {
        // Simuler une erreur de cache en utilisant un mock
        $mockCache = $this->createMock(ArrayAdapter::class);
        $mockCache->method('deleteItem')
            ->willThrowException(new \Exception('Delete error'));

        $service = new StatisticsCacheService($mockCache);

        // Ne devrait pas lever d'exception
        $service->clearCache();
        $this->assertTrue(true); // Si on arrive ici, le test est réussi
    }
}
