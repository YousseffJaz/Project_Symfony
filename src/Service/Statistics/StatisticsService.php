<?php

namespace App\Service\Statistics;

use App\Repository\OrderRepository;
use App\Repository\StockListRepository;
use App\Service\Cache\StatisticsCacheService;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Enum\OrderStatus;

class StatisticsService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private StockListRepository $stockListRepository,
        private StatisticsCacheService $cacheService
    ) {
    }

    public function calculateDailyStats(int $month, int $year): array
    {
        return $this->cacheService->getDailyStats($month, $year, function() use ($month, $year) {
            $dailyData = [];
            $currentMonthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            
            // Récupérer toutes les statistiques en une seule requête
            $stats = $this->orderRepository->findDailyStatsByMonth($month, $year);
            
            // Créer un tableau associatif pour un accès facile
            $dailyStats = [];
            foreach ($stats as $stat) {
                // Convertir le jour en entier pour enlever les zéros au début
                $day = (int)$stat['day'];
                $dailyStats[$day] = $stat['total'];
            }
            
            // Remplir les données pour chaque jour
            for ($i = 1; $i <= $currentMonthDays; $i++) {
                $dailyData[] = [
                    'total' => number_format($dailyStats[$i] ?? 0, 2, '.', ''),
                    'url' => '/admin/orders/filter/day/' . sprintf('%04d-%02d-%02d', $year, $month, $i)
                ];
            }
            
            return $dailyData;
        });
    }

    public function calculateMonthlyStats(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        return $this->cacheService->getMonthlyStats($startDate, $endDate, function() use ($startDate, $endDate) {
            if (!$startDate) {
                $startDate = new \DateTime('first day of -11 months');
            }
            if (!$endDate) {
                $endDate = new \DateTime('last day of this month');
            }

            $stats = $this->orderRepository->getMonthlyStats($startDate, $endDate);
            
            $monthlyData = [];
            $labels = [];
            $total = 0;
            
            $current = clone $startDate;
            while ($current <= $endDate) {
                $monthKey = $current->format('Y-m');
                $monthlyData[$monthKey] = [
                    'total' => '0.00',
                    'url' => '/admin/orders/filter/month/' . $current->format('Y-m')
                ];
                $labels[] = $this->getMonthName((int)$current->format('m')) . ' ' . $current->format('Y');
                $current->modify('+1 month');
            }

            foreach ($stats as $stat) {
                $monthKey = $stat['year'] . '-' . str_pad($stat['month'], 2, '0', STR_PAD_LEFT);
                if (isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey]['total'] = number_format($stat['total'], 2, '.', '');
                    $total += $stat['total'];
                }
            }

            return [
                'data' => array_values($monthlyData),
                'labels' => $labels,
                'total' => $total
            ];
        });
    }

    public function calculatePaymentStats(): array
    {
        return $this->cacheService->getPaymentStats(function() {
            $orders = $this->orderRepository->findAll();
            
            $methodStats = array_fill(0, 8, 0); // Initialize array for 8 payment methods
            $typeStats = array_fill(0, 2, 0);   // Initialize array for 2 payment types
            $statusStats = array_fill(0, 4, 0);  // Initialize array for 4 status types
            
            foreach ($orders as $order) {
                $methodStats[$order->getPaymentMethod()] += $order->getTotal();
                $typeStats[$order->getPaymentType()] += $order->getTotal();
                $statusStats[$order->getStatus()] += $order->getTotal();
            }
            
            return [
                'methods' => $methodStats,
                'types' => $typeStats,
                'status' => $statusStats
            ];
        });
    }

    public function calculateStockValue(): float
    {
        return $this->cacheService->getStockValue(function() {
            $stocks = $this->stockListRepository->findAll();
            $totalValue = 0;
            
            foreach ($stocks as $stock) {
                $product = $stock->getProduct();
                if ($product && $stock->getQuantity() > 0) {
                    $totalValue += $stock->getQuantity() * $product->getPrice();
                }
            }
            
            return $totalValue;
        });
    }

    public function getBestSellers(): array
    {
        return $this->cacheService->getBestSellers(function() {
            return [
                'products' => $this->orderRepository->findBestProducts(),
                'categories' => $this->orderRepository->findBestCategories(),
                'customers' => $this->orderRepository->findBestCustomers()
            ];
        });
    }

    public function getUnpaidAmount(\DateTime $startDate, \DateTime $endDate): float
    {
        return $this->cacheService->getUnpaidAmount(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            function() use ($startDate, $endDate) {
                $orders = $this->orderRepository->findByStartAndEnd(
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                );
                $notPaid = 0;
                foreach ($orders as $order) {
                    $notPaid += $order->getTotal() - $order->getPaid();
                }
                return $notPaid;
            }
        );
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars',
            4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
            10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $months[$month] ?? '';
    }
} 