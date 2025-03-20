<?php

namespace App\Controller\Admin;

use App\Repository\FluxRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminStatistiqueController extends AbstractController
{
   /**
   * Permet d'afficher les statistiques
   */
   #[Route('/admin/statistiques', name: 'admin_statistique_index')]
   #[IsGranted('ROLE_ADMIN')]
   public function index(
       Request $request, 
       OrderRepository $orderRepo,
       ProductRepository $productRepo,
       CategoryRepository $categoryRepo,
       StockListRepository $stockRepo,
       PriceListRepository $priceRepo
   ): Response
   {
       $labels = [];
       $data = [];
       $total = 0;
       $month = (int)date('m');
       $year = (int)date('Y');
       
       // Get start and end dates from request or default to current year
       $start = $request->query->get('start', $year . '-01-01');
       $end = $request->query->get('end', date('Y-m-t'));
       
       // Daily data for the current month
       $dailyData = [];
       $currentMonthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
       for ($i = 1; $i <= $currentMonthDays; $i++) {
           $orders = $orderRepo->findByDay($i, $month, $year);
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
       
       // Monthly data for the current year or between start/end dates
       $monthlyData = [];
       $labels = [];
       $total = 0;
       
       $currentYear = (int)date('Y');
       
       if ($start && $end) {
           // Use start and end dates if provided
           $startDate = new \DateTime($start);
           $endDate = new \DateTime($end);
           
           // Clone startDate to avoid modifying it in the loop
           $currentDate = clone $startDate;
           
           while ($currentDate <= $endDate) {
               $targetMonth = (int)$currentDate->format('m');
               $targetYear = (int)$currentDate->format('Y');
               
               $orders = $orderRepo->findByMonth($targetMonth, $targetYear);
               $amount = 0;
               
               if ($orders) {
                   foreach ($orders as $order) {
                       $amount += (float)$order->getTotal();
                   }
               }
               
               $monthlyData[] = [
                   'total' => number_format($amount, 2, '.', ''),
                   'url' => '/admin/orders/month?month=' . $targetMonth . '&year=' . $targetYear
               ];
               $total += $amount;
               $labels[] = $this->getMonthName($targetMonth) . ' ' . $targetYear;
               
               // Move to next month
               $currentDate->modify('first day of next month');
           }
       } else {
           // Default: show months of current year
           for ($month = 1; $month <= 12; $month++) {
               $orders = $orderRepo->findByMonth($month, $currentYear);
               $amount = 0;
               
               if ($orders) {
                   foreach ($orders as $order) {
                       $amount += (float)$order->getTotal();
                   }
               }
               
               $monthlyData[] = [
                   'total' => number_format($amount, 2, '.', ''),
                   'url' => '/admin/orders/month?month=' . $month . '&year=' . $currentYear
               ];
               $total += $amount;
               $labels[] = $this->getMonthName($month);
           }
       }

       // Get total amount
       $totalStats = $orderRepo->totalAmount();
       
       // Get current month amount
       $monthStats = $orderRepo->totalAmountByStartAndEnd(
           date('Y-m-01'),
           date('Y-m-t')
       );

       // Calculate stock value
       $products = $productRepo->findBy(['archive' => false, 'digital' => false]);
       $stocks = $stockRepo->findStockName();
       $stockValue = 0;

       foreach ($stocks as $stock) {
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
                       $stockValue = $stockValue + ($items['quantity'] * round($price, 2));
                   }
               }
           }
       }

       // Get payment method statistics
       $paymentMethodStats = [];
       for ($i = 0; $i <= 7; $i++) {
           $orders = $orderRepo->findByPaymentMethod($i);
           $amount = 0;
           foreach ($orders as $order) {
               $amount += (float)$order->getTotal();
           }
           $paymentMethodStats[$i] = $amount;
       }

       // Get payment type statistics
       $paymentTypeStats = [];
       for ($i = 0; $i <= 2; $i++) {
           $orders = $orderRepo->findByPaymentType($i);
           $amount = 0;
           foreach ($orders as $order) {
               $amount += (float)$order->getTotal();
           }
           $paymentTypeStats[$i] = $amount;
       }

       // Get order status statistics
       $orderStatusStats = [];
       for ($i = 0; $i <= 3; $i++) {
           $orders = $orderRepo->findByStatus($i);
           $amount = 0;
           foreach ($orders as $order) {
               $amount += (float)$order->getTotal();
           }
           $orderStatusStats[$i] = $amount;
       }

       // Get best products and categories
       $bestProducts = $orderRepo->findBestProducts();
       $bestCategories = $orderRepo->findBestCategories();
       $bestCustomers = $orderRepo->findBestCustomers();

       // Structure data for charts
       $array = [
           'labels' => range(1, $currentMonthDays),
           'amounts' => $dailyData
       ];

       $array2 = [
           'labels' => $labels,
           'amounts' => $monthlyData
       ];

       return $this->render('admin/statistiques/index.html.twig', [
           'labels' => json_encode($labels),
           'data' => json_encode($data),
           'total' => $totalStats,
           'month' => $monthStats,
           'annual' => $total,
           'start' => $start,
           'end' => $end,
           'profit' => 0, // You'll need to calculate this based on your business logic
           'stock' => $stockValue,
           'orders' => count($orderRepo->findAll()),
           'notPaid' => 0, // You'll need to calculate this
           'expenses' => [['amount' => 0]], // You'll need to get this from your expense repository
           'bestProducts' => $bestProducts,
           'bestCategories' => $bestCategories,
           'bestCustomers' => $bestCustomers,
           'array' => $array,
           'array2' => $array2,
           // Payment method statistics
           'cash' => $paymentMethodStats[0],
           'transcash' => $paymentMethodStats[1],
           'card' => $paymentMethodStats[2],
           'paypal' => $paymentMethodStats[3],
           'pcs' => $paymentMethodStats[4],
           'check' => $paymentMethodStats[5],
           'paysafecard' => $paymentMethodStats[6],
           'bank' => $paymentMethodStats[7],
           // Payment type statistics
           'online' => $paymentTypeStats[0],
           'local' => $paymentTypeStats[1],
           'delivery' => $paymentTypeStats[2],
           // Order status statistics
           'waiting' => $orderStatusStats[0],
           'partial' => $orderStatusStats[1],
           'paid' => $orderStatusStats[2],
           'refund' => $orderStatusStats[3]
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
   public function stocks(OrderRepository $orderRepo, LineItemRepository $lineItemRepo, Request $request, ObjectManager $manager, FluxRepository $fluxRepo, ProductRepository $productRepo, PriceListRepository $priceRepo, StockListRepository $stockRepo, TransactionRepository $transactionRepo) {
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