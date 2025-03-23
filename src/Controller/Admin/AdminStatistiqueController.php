<?php

namespace App\Controller\Admin;

use App\Service\Statistics\StatisticsService;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PriceListRepository;
use App\Repository\StockListRepository;
use App\Repository\FluxRepository;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Enum\OrderStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminStatistiqueController extends AbstractController
{
    public function __construct(
        private StatisticsService $statisticsService,
        private OrderRepository $orderRepository,
        private FluxRepository $fluxRepository
    ) {
    }

    /**
    * Permet d'afficher les statistiques
    */
    #[Route('/admin/statistiques', name: 'admin_statistique_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $month = (int)date('m');
        $year = (int)date('Y');
        
        // Get start and end dates from request or default to all time
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        
        if (!$start || !$end) {
            // Si pas de dates spécifiées, on prend tout
            $startDate = new \DateTime('2000-01-01');
            $endDate = new \DateTime();
        } else {
            $startDate = new \DateTime($start);
            $endDate = new \DateTime($end);
        }
        
        // Get daily stats for current month
        $dailyStats = $this->statisticsService->calculateDailyStats($month, $year);
        
        // Get monthly stats for last 12 months
        $monthlyStatsStart = (new \DateTime())->modify('-11 months')->modify('first day of this month');
        $monthlyStatsEnd = new \DateTime();
        $monthlyStats = $this->statisticsService->calculateMonthlyStats($monthlyStatsStart, $monthlyStatsEnd);
        
        // Get payment stats
        $paymentStats = $this->statisticsService->calculatePaymentStats();
        
        // Get stock value
        $stockValue = $this->statisticsService->calculateStockValue();
        
        // Get best sellers
        $bestSellers = $this->statisticsService->getBestSellers();
        
        // Get total stats
        $totalStats = $this->orderRepository->totalAmount();
        
        // Get current month stats
        $monthStats = $this->orderRepository->totalAmountByStartAndEnd(
            date('Y-m-01'),
            date('Y-m-t')
        );

        // Get expenses for the period
        $expenses = $this->fluxRepository->totalAmountStartAndEnd(1, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        // Get unpaid amount for the period
        $orders = $this->orderRepository->findByStartAndEnd($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
        $notPaid = 0;
        foreach ($orders as $order) {
            $notPaid += $order->getTotal() - $order->getPaid();
        }

        // Structure data for charts
        $array = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)),
            'amounts' => $dailyStats
        ];

        $array2 = [
            'labels' => $monthlyStats['labels'],
            'amounts' => $monthlyStats['data']
        ];

        return $this->render('admin/statistiques/index.html.twig', [
            'labels' => json_encode($monthlyStats['labels']),
            'data' => json_encode([]),
            'total' => $totalStats,
            'month' => $monthStats,
            'annual' => $monthlyStats['total'],
            'start' => $start,
            'end' => $end,
            'profit' => $totalStats[0]['total'] - ($expenses[0]['amount'] ?? 0),
            'stock' => $stockValue,
            'orders' => count($this->orderRepository->findAll()),
            'notPaid' => $notPaid,
            'expenses' => $expenses,
            'bestProducts' => $bestSellers['products'],
            'bestCategories' => $bestSellers['categories'],
            'bestCustomers' => $bestSellers['customers'],
            'array' => $array,
            'array2' => $array2,
            // Payment method statistics
            'cash' => $paymentStats['methods'][PaymentMethod::CASH->value],
            'transcash' => $paymentStats['methods'][PaymentMethod::TRANSCASH->value],
            'card' => $paymentStats['methods'][PaymentMethod::CARD->value],
            'paypal' => $paymentStats['methods'][PaymentMethod::PAYPAL->value],
            'pcs' => $paymentStats['methods'][PaymentMethod::PCS->value],
            'check' => $paymentStats['methods'][PaymentMethod::CHECK->value],
            'paysafecard' => $paymentStats['methods'][PaymentMethod::PAYSAFECARD->value],
            'bank' => $paymentStats['methods'][PaymentMethod::BANK->value],
            // Payment type statistics
            'online' => $paymentStats['types'][PaymentType::ONLINE->value],
            'local' => $paymentStats['types'][PaymentType::LOCAL->value],
            // Order status statistics
            'waiting' => $paymentStats['status'][OrderStatus::WAITING->value],
            'partial' => $paymentStats['status'][OrderStatus::PARTIAL->value],
            'paid' => $paymentStats['status'][OrderStatus::PAID->value],
            'refund' => $paymentStats['status'][OrderStatus::REFUND->value]
        ]);
    }

    #[Route('/admin/statistiques/month', name: 'admin_statistique_month')]
    #[IsGranted('ROLE_ADMIN')]
    public function month(Request $request, OrderRepository $orderRepo): Response
    {
        $month = (int)$request->query->get('month', date('m'));
        $year = (int)$request->query->get('year', date('Y'));
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $labels = [];
        $data = [];
        $total = 0;

        for ($i = 1; $i <= $days; $i++) {
            $orders = $orderRepo->findByDay($i, $month, $year);
            $amount = 0;
            
            if ($orders) {
                foreach ($orders as $order) {
                    $amount += (float)$order->getTotal();
                }
            }
            
            $data[] = number_format($amount, 2, '.', '');
            $total += $amount;
            $labels[] = $i;
        }

        return $this->render('admin/statistique/month.html.twig', [
            'labels' => json_encode($labels),
            'data' => json_encode($data),
            'total' => number_format($total, 2, '.', ' '),
            'month' => $this->getMonthName($month),
            'year' => $year
        ]);
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre'
        ];
        
        return $months[$month] ?? '';
    }

    /**
    * Permet d'afficher la valeur des stocks
    */
    #[Route('/admin/statistiques/stocks', name: 'admin_statistiques_stocks')]
    #[IsGranted('ROLE_ADMIN')]
    public function stocks(
        ProductRepository $productRepo,
        PriceListRepository $priceRepo,
        StockListRepository $stockRepo
    ) {
        $products = $productRepo->findBy(['archive' => false, 'digital' => false ]);
        $stocks = $stockRepo->findStockName();
        $array = [];

        foreach ($stocks as $key=>$stock) {
            $amount = 0;
            foreach ($products as $product) {
                $quantity = $stockRepo->findQuantityByProductAndStock($product, $stock);
                $listPrices = $priceRepo->findByProduct($product);
                if ($listPrices && $stocks) {
                    foreach ($quantity as $items) {
                        $price = 0;
                        foreach ($listPrices as $list) {
                            $price = $price + $list['price'];
                        }
                        $price = $price / sizeof($listPrices);
                        $amount = $amount + ($items['quantity'] * round($price, 2));
                    }
                }
            }
            $array[] = [ 'name' => $stock['name'], 'amount' => $amount ];
        }

        return $this->render('admin/statistiques/stocks.html.twig', [
            'array' => $array,
        ]);
    }

    public static function dateToFrench($date) {

        $french_months = ["Janv.", "Févr.", "Mars", "Avr.", "Mai", "Juin", "Juil.", "Aoùt", "Sept.", "Oct.", "Nov.", "Déc."];
        $english_months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        return str_replace($english_months, $french_months, $date);
    }
}