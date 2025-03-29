<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class StatisticsCacheService
{
    private const DAILY_STATS_KEY = 'stats_daily_%d_%d'; // month, year
    private const MONTHLY_STATS_KEY = 'stats_monthly_%s_%s'; // start_date, end_date
    private const PAYMENT_STATS_KEY = 'stats_payment';
    private const STOCK_VALUE_KEY = 'stats_stock_value';
    private const BEST_SELLERS_KEY = 'stats_best_sellers';
    private const TOTAL_AMOUNT_KEY = 'stats_total_amount';
    private const MONTHLY_AMOUNT_KEY = 'stats_monthly_amount_%s_%s'; // start, end
    private const EXPENSES_KEY = 'stats_expenses_%s_%s_%d'; // start, end, type
    private const UNPAID_AMOUNT_KEY = 'stats_unpaid_amount_%s_%s'; // start, end
    private const TOTAL_ORDERS_COUNT_KEY = 'stats_total_orders_count';
    private const CACHE_TTL = 3600; // 1 heure

    public function __construct(
        private AdapterInterface $cache,
    ) {
    }

    public function getDailyStats(int $month, int $year, callable $callback): array
    {
        $key = sprintf(self::DAILY_STATS_KEY, $month, $year);

        try {
            $cacheItem = $this->cache->getItem($key);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getMonthlyStats(\DateTime $startDate, \DateTime $endDate, callable $callback): array
    {
        $key = sprintf(self::MONTHLY_STATS_KEY, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        try {
            $cacheItem = $this->cache->getItem($key);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getPaymentStats(callable $callback): array
    {
        try {
            $cacheItem = $this->cache->getItem(self::PAYMENT_STATS_KEY);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getStockValue(callable $callback): float
    {
        try {
            $cacheItem = $this->cache->getItem(self::STOCK_VALUE_KEY);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getBestSellers(callable $callback): array
    {
        try {
            $cacheItem = $this->cache->getItem(self::BEST_SELLERS_KEY);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getTotalAmount(callable $callback): array
    {
        try {
            $cacheItem = $this->cache->getItem(self::TOTAL_AMOUNT_KEY);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getMonthlyAmount(string $start, string $end, callable $callback): array
    {
        $key = sprintf(self::MONTHLY_AMOUNT_KEY, $start, $end);

        try {
            $cacheItem = $this->cache->getItem($key);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getExpenses(int $type, string $start, string $end, callable $callback): array
    {
        $key = sprintf(self::EXPENSES_KEY, $start, $end, $type);

        try {
            $cacheItem = $this->cache->getItem($key);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function getUnpaidAmount(string $start, string $end, callable $callback): float
    {
        $key = sprintf(self::UNPAID_AMOUNT_KEY, $start, $end);

        try {
            $cacheItem = $this->cache->getItem($key);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function clearCache(): void
    {
        try {
            $this->cache->deleteItem(self::PAYMENT_STATS_KEY);
            $this->cache->deleteItem(self::STOCK_VALUE_KEY);
            $this->cache->deleteItem(self::BEST_SELLERS_KEY);
            $this->cache->deleteItem(self::TOTAL_AMOUNT_KEY);
            $this->cache->deleteItem(self::TOTAL_ORDERS_COUNT_KEY);
        } catch (\Exception $e) {
        }
    }

    public function getTotalOrdersCount(callable $callback): int
    {
        try {
            $cacheItem = $this->cache->getItem(self::TOTAL_ORDERS_COUNT_KEY);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $callback();

            $cacheItem->set($result);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            return $result;
        } catch (\Exception $e) {
            return $callback();
        }
    }
}
