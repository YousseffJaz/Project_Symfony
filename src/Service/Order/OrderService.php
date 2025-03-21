<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\Admin;
use App\Repository\OrderRepository;
use App\Repository\LineItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private LineItemRepository $lineItemRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getOrdersByDateRange(\DateTime $start, \DateTime $end): array
    {
        return $this->orderRepository->findByStartAndEnd(
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        );
    }

    public function getTotalsByDateRange(\DateTime $start, \DateTime $end): array
    {
        $orders = $this->getOrdersByDateRange($start, $end);
        $total = $this->lineItemRepository->totalAmountByStartAndEnd(
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        );

        $alreadyPaid = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $alreadyPaid += $order->getPaid();
            }
        }

        return [
            'orders' => $orders,
            'total' => $total[0]['total'] ?? 0,
            'alreadyPaid' => $alreadyPaid
        ];
    }

    public function getOrdersByStatus(string $status): array
    {
        $orders = $this->orderRepository->findByStatus($status);
        return $this->calculateOrderTotals($orders);
    }

    public function getOrdersByPaymentType(string $paymentType): array
    {
        $orders = $this->orderRepository->findByPaymentType($paymentType);
        return $this->calculateOrderTotals($orders);
    }

    public function getOrdersByPaymentMethod(string $paymentMethod): array
    {
        $orders = $this->orderRepository->findByPaymentMethod($paymentMethod);
        return $this->calculateOrderTotals($orders);
    }

    public function getWaitingOrders(): array
    {
        $orders = $this->orderRepository->findOrderNotComplete();
        return $this->calculateOrderTotals($orders);
    }

    private function calculateOrderTotals(array $orders): array
    {
        $total = 0;
        $alreadyPaid = 0;

        foreach ($orders as $order) {
            $total += $order->getTotal();
            $alreadyPaid += $order->getPaid();
        }

        return [
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid
        ];
    }
} 