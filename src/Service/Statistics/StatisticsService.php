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
        
        for ($i = 1; $i <= $currentMonthDays; $i++) {
            $orders = $this->orderRepository->findByDay($i, $month, $year);
            $amount = 0;
            
            if ($orders) {
                foreach ($orders as $order) {
                    $amount += (float)$order->getTotal();
                }
            }
            
            $dailyData[] = [
                'total' => number_format($amount, 2, '.', ''),
                'url' => '/admin/orders/day?day=' . sprintf('%04d-%02d-%02d', $year, $month, $i)
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
                'url' => '/admin/orders/month?month=' . $month . '&year=' . $year
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
        $products = $this->productRepository->findBy(['archive' => false, 'digital' => false]);
        $stocks = $this->stockListRepository->findStockName();
        $stockValue = 0;

        foreach ($stocks as $stock) {
            foreach ($products as $product) {
                $quantity = $this->stockListRepository->findQuantityByProductAndStock($product, $stock);
                $listPrices = $this->priceListRepository->findByProduct($product);
                if ($listPrices && $stocks) {
                    foreach ($quantity as $items) {
                        $price = 0;
                        foreach ($listPrices as $list) {
                            $price = $price + $list['price'];
                        }
                        $price = $price / sizeof($listPrices);
                        $stockValue = $stockValue + ($items['quantity'] * round($price, 2));
                    }
                }
            }
        }

        return $stockValue;
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