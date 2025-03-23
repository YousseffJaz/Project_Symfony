<?php

namespace App\Service\Cache;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class StatisticsCacheService
{
    private const DAILY_STATS_KEY = 'stats_daily_%d_%d'; // month, year
    private const MONTHLY_STATS_KEY = 'stats_monthly_%s_%s'; // start_date, end_date
    private const PAYMENT_STATS_KEY = 'stats_payment';
    private const STOCK_VALUE_KEY = 'stats_stock_value';
    private const BEST_SELLERS_KEY = 'stats_best_sellers';
    private const CACHE_TTL = 3600; // 1 heure

    public function __construct(
        private CacheInterface $cache
    ) {
    }

    public function getDailyStats(int $month, int $year, callable $callback): array
    {
        $key = sprintf(self::DAILY_STATS_KEY, $month, $year);
        
        return $this->cache->get($key, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::CACHE_TTL);
            return $callback();
        });
    }

    public function getMonthlyStats(\DateTime $startDate, \DateTime $endDate, callable $callback): array
    {
        $key = sprintf(self::MONTHLY_STATS_KEY, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
        
        return $this->cache->get($key, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::CACHE_TTL);
            return $callback();
        });
    }

    public function getPaymentStats(callable $callback): array
    {
        return $this->cache->get(self::PAYMENT_STATS_KEY, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::CACHE_TTL);
            return $callback();
        });
    }

    public function getStockValue(callable $callback): float
    {
        return $this->cache->get(self::STOCK_VALUE_KEY, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::CACHE_TTL);
            return $callback();
        });
    }

    public function getBestSellers(callable $callback): array
    {
        return $this->cache->get(self::BEST_SELLERS_KEY, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::CACHE_TTL);
            return $callback();
        });
    }

    public function clearCache(): void
    {
        $this->cache->delete(self::PAYMENT_STATS_KEY);
        $this->cache->delete(self::STOCK_VALUE_KEY);
        $this->cache->delete(self::BEST_SELLERS_KEY);
        // Les clés dynamiques seront automatiquement nettoyées par l'expiration
    }
} 