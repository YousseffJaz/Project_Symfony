<?php

namespace App\Service\Statistics;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Enum\OrderStatus;

class StatisticsService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private StockListRepository $stockListRepository,
        private PriceListRepository $priceListRepository
    ) {
    }

    public function calculateDailyStats(int $month, int $year): array
    {
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
    }

    public function calculateMonthlyStats(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $monthlyData = [];
        $labels = [];
        $total = 0;
        
        if ($startDate && $endDate) {
            $currentDate = clone $startDate;
            
            while ($currentDate <= $endDate) {
                $targetMonth = (int)$currentDate->format('m');
                $targetYear = (int)$currentDate->format('Y');
                
                $monthStats = $this->calculateMonthStats($targetMonth, $targetYear);
                $monthlyData[] = $monthStats['data'];
                $total += $monthStats['amount'];
                $labels[] = $this->getMonthName($targetMonth) . ' ' . $targetYear;
                
                $currentDate->modify('first day of next month');
            }
        } else {
            $currentYear = (int)date('Y');
            for ($month = 1; $month <= 12; $month++) {
                $monthStats = $this->calculateMonthStats($month, $currentYear);
                $monthlyData[] = $monthStats['data'];
                $total += $monthStats['amount'];
                $labels[] = $this->getMonthName($month);
            }
        }
        
        return [
            'data' => $monthlyData,
            'labels' => $labels,
            'total' => $total
        ];
    }

    private function calculateMonthStats(int $month, int $year): array
    {
        $orders = $this->orderRepository->findByMonth($month, $year);
        $amount = 0;
        
        if ($orders) {
            foreach ($orders as $order) {
                $amount += (float)$order->getTotal();
            }
        }
        
        return [
            'data' => [
                'total' => number_format($amount, 2, '.', ''),
                'url' => '/admin/orders/filter/month/' . sprintf('%04d-%02d', $year, $month)
            ],
            'amount' => $amount
        ];
    }

    public function calculatePaymentStats(): array
    {
        $methodStats = [];
        $typeStats = [];
        $statusStats = [];
        
        // Payment methods
        foreach (PaymentMethod::cases() as $method) {
            $orders = $this->orderRepository->findByPaymentMethod($method->value);
            $amount = 0;
            foreach ($orders as $order) {
                $amount += (float)$order->getTotal();
            }
            $methodStats[$method->value] = $amount;
        }
        
        // Payment types
        foreach (PaymentType::cases() as $type) {
            $orders = $this->orderRepository->findByPaymentType($type->value);
            $amount = 0;
            foreach ($orders as $order) {
                $amount += (float)$order->getTotal();
            }
            $typeStats[$type->value] = $amount;
        }
        
        // Order status
        foreach (OrderStatus::cases() as $status) {
            $orders = $this->orderRepository->findByStatus($status->value);
            $amount = 0;
            foreach ($orders as $order) {
                $amount += (float)$order->getTotal();
            }
            $statusStats[$status->value] = $amount;
        }
        
        return [
            'methods' => $methodStats,
            'types' => $typeStats,
            'status' => $statusStats
        ];
    }

    public function calculateStockValue(): float
    {
        $stockValues = $this->stockListRepository->calculateStockValue();
        return array_sum(array_column($stockValues, 'value'));
    }

    public function getBestSellers(): array
    {
        return [
            'products' => $this->orderRepository->findBestProducts(),
            'categories' => $this->orderRepository->findBestCategories(),
            'customers' => $this->orderRepository->findBestCustomers()
        ];
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