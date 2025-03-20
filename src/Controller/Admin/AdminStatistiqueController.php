<?php

namespace App\Controller\Admin;

use App\Repository\FluxRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Repository\NoteRepository;
use App\Repository\LineItemRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ObjectManager;
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
   public function index(Request $request, OrderRepository $orderRepo): Response
   {
       $labels = [];
       $data = [];
       $total = 0;
       $month = (int)date('m');
       $year = (int)date('Y');
       
       for ($i = 1; $i <= $month; $i++) {
           $orders = $orderRepo->findByMonth($i, $year);
           $amount = 0;
           
           if ($orders) {
               foreach ($orders as $order) {
                   $amount += (float)$order->getTotal();
               }
           }
           
           $data[] = number_format($amount, 2, '.', '');
           $total += $amount;
           $labels[] = $this->getMonthName($i);
       }

       return $this->render('admin/statistique/index.html.twig', [
           'labels' => json_encode($labels),
           'data' => json_encode($data),
           'total' => number_format($total, 2, '.', ' '),
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