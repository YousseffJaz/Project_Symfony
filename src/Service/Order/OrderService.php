<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\Admin;
use App\Entity\LineItem;
use App\Entity\OrderHistory;
use App\Repository\OrderRepository;
use App\Repository\LineItemRepository;
use App\Repository\StockListRepository;
use App\Repository\TransactionRepository;
use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private LineItemRepository $lineItemRepository,
        private EntityManagerInterface $entityManager,
        private StockListRepository $stockListRepository,
        private TransactionRepository $transactionRepository
    ) {
    }

    /**
     * Récupère les commandes filtrées selon différents critères
     */
    public function getFilteredOrders(?string $filterType = null, ?string $filterValue = null): array
    {
        $orders = match($filterType) {
            'status' => $this->orderRepository->findBy(['status' => $filterValue]),
            'payment_type' => $this->orderRepository->findByPaymentType($filterValue),
            'payment_method' => $this->orderRepository->findByPaymentMethod($filterValue),
            'expedition' => $this->orderRepository->findByExpedition(),
            'impayee' => $this->orderRepository->findByImpayee(),
            'waiting' => $this->orderRepository->findOrderNotComplete(),
            'day' => $this->orderRepository->findByDay(
                (int)(new \DateTime($filterValue))->format('d'),
                (int)(new \DateTime($filterValue))->format('m'),
                (int)(new \DateTime($filterValue))->format('Y')
            ),
            'month' => $this->orderRepository->findByMonth(
                (int)substr($filterValue, -2),
                (int)substr($filterValue, 0, 4)
            ),
            default => []
        };

        return $this->calculateOrderTotals($orders);
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

    /**
     * Calcule les totaux pour une liste de commandes
     */
    public function calculateTotals(array $orders): array
    {
        $total = 0;
        $alreadyPaid = 0;

        foreach ($orders as $order) {
            $total += $order->getTotal();
            $alreadyPaid += $order->getPaid();
        }

        return [
            'total' => $total,
            'alreadyPaid' => $alreadyPaid
        ];
    }

    private function calculateOrderTotals(array $orders): array
    {
        $totals = $this->calculateTotals($orders);
        return [
            'orders' => $orders,
            'total' => $totals['total'],
            'alreadyPaid' => $totals['alreadyPaid']
        ];
    }

    /**
     * Prépare les données communes pour le rendu du template index
     */
    public function prepareIndexViewData(array $orders, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $totals = $this->calculateTotals($orders);
        
        if (!$start) {
            $start = new \DateTime('now');
        }
        if (!$end) {
            $end = new \DateTime('now');
        }

        return [
            'search' => '',
            'orders' => $orders,
            'total' => $totals['total'],
            'alreadyPaid' => $totals['alreadyPaid'],
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ];
    }

    /**
     * Supprime une commande et gère les stocks associés
     */
    public function deleteOrder(Order $order): void
    {
        $lineItems = $order->getLineItems();
        
        if ($lineItems) {
            foreach ($lineItems as $lineItem) {
                $stock = $lineItem->getStock();
                if ($stock) {
                    $quantity = $lineItem->getQuantity();
                    $this->stockListRepository->incrementStock($stock, $quantity);
                }
            }
        }

        $transaction = $this->transactionRepository->findOneByInvoice($order);
        if ($transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }

    /**
     * Supprime un élément de ligne et met à jour les stocks
     */
    public function deleteLineItem(LineItem $lineItem, Admin $admin): void
    {
        $stock = $lineItem->getStock();
        if ($stock) {
            $quantity = $lineItem->getQuantity();
            $this->stockListRepository->incrementStock($stock, $quantity);
            $order = $lineItem->getOrder();
            $order->setTotal($order->getTotal() - $lineItem->getPrice());
        }

        $history = new OrderHistory();
        $history->setTitle("Le produit '{$lineItem->getTitle()}' en '{$lineItem->getQuantity()}' exemplaire(s) pour '{$lineItem->getPrice()}€' a été supprimé");
        $history->setInvoice($lineItem->getOrder());
        $history->setAdmin($admin);
        
        $this->entityManager->persist($history);
        $this->entityManager->remove($lineItem);
        $this->entityManager->flush();
    }

    /**
     * Récupère les commandes groupées par client
     */
    public function getCustomerOrders(): array
    {
        $customers = $this->orderRepository->groupByCustomers();
        $result = [];

        foreach ($customers as $customer) {
            $orders = $this->orderRepository->findByFirstname($customer['firstname']);
            $result[] = [
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'number' => $customer['number'],
                'email' => $customer['email'],
                'orders' => $orders
            ];
        }

        return $result;
    }
} 