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
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminStatistiqueController extends AbstractController
{
   /**
   * Permet d'afficher les statistiques
   *
   * @Route("/admin/statistiques", name="admin_statistiques")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function statistiques(OrderRepository $orderRepo, LineItemRepository $lineItemRepo, Request $request, ObjectManager $manager, FluxRepository $fluxRepo, ProductRepository $productRepo, PriceListRepository $priceRepo, StockListRepository $stockRepo, CategoryRepository $categoryRepo, TransactionRepository $transactionRepo, NoteRepository $noteRepo) {
   	$start = $request->query->get('start');
   	$end = $request->query->get('end');
   	$date1 = new \DateTime('now', timezone_open('Europe/Paris'));
   	$note = $transactionRepo->totalAmount();
   	$note ? $note = $note[0]['total'] : $note = 0; $notPaid = 0;
   	$amounts = [];

   	if ($start && $end) {
   		$total = $orderRepo->totalAmountByStartAndEnd($start, $end);
   		$orders = count($orderRepo->findByStartAndEnd($start, $end));
   		$online = count($orderRepo->findByPaymentTypeAndStartAndEnd(0, $start, $end));
   		$local = count($orderRepo->findByPaymentTypeAndStartAndEnd(1, $start, $end));
      $delivery = count($orderRepo->findByPaymentTypeAndStartAndEnd(2, $start, $end));
   		$cash = count($orderRepo->findByPaymentMethodAndStartAndEnd(0, $start, $end));
   		$transcash = count($orderRepo->findByPaymentMethodAndStartAndEnd(1, $start, $end));
   		$card = count($orderRepo->findByPaymentMethodAndStartAndEnd(2, $start, $end));
   		$paypal = count($orderRepo->findByPaymentMethodAndStartAndEnd(3, $start, $end));
   		$pcs = count($orderRepo->findByPaymentMethodAndStartAndEnd(4, $start, $end));
   		$check = count($orderRepo->findByPaymentMethodAndStartAndEnd(5, $start, $end));
   		$paysafecard = count($orderRepo->findByPaymentMethodAndStartAndEnd(6, $start, $end));
   		$bank = count($orderRepo->findByPaymentMethodAndStartAndEnd(7, $start, $end));
   		$waiting = count($orderRepo->findByStatusAndStartAndEnd(0, $start, $end));
   		$partial = count($orderRepo->findByStatusAndStartAndEnd(1, $start, $end));
   		$paid = count($orderRepo->findByStatusAndStartAndEnd(2, $start, $end));
   		$refund = count($orderRepo->findByStatusAndStartAndEnd(3, $start, $end));
   		$bestProducts = $orderRepo->findBestProductsStartAndEnd($start, $end);
   		$bestCategories = $orderRepo->findBestCategoriesStartAndEnd($start, $end);
   		$bestCustomers = $orderRepo->findBestCustomersStartAndEnd($start, $end);
   		$expenses = $fluxRepo->totalAmountStartAndEnd(1, $start, $end);
   		$results = $orderRepo->findByStartAndEnd($start, $end);
   		$profit = 0;

   		foreach ($results as $result) {
   			$profit = $profit + ($result->getTotal() - $result->getShippingCost());
   			foreach ($result->getLineItems() as $line) {
   				$profit = $profit - ($line->getQuantity() * $line->getProduct()->getPurchasePrice());
   			}
   		}

   		$now = \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'));
   		$now2 = \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'));
   		$days = $now2->diff($now)->format("%a") + 1;

   		for ($i = 0; $i < $days; $i++) {
   			$labels[] = $this->dateToFrench($now->format('d M'));
   			$amount = $orderRepo->totalAmountByDay($now->format('Y-m-d'));
   			$url = "/admin/orders?start=". $now->format('Y-m-d') . "&end=". $now->format('Y-m-d');
   			$now->modify('+1 day');

   			if ($amount[0]['total']) {
   				$amounts[] = [ 'total' => str_replace(",", ".", round($amount[0]['total'], 2)), 'url' => $url ];
   			} else {
   				$amounts[] = [ 'total' => 0, 'url' => $url ];
   			}
   		}
   		$array['labels'] = $labels;
   		$array['amounts'] = $amounts;

   		$category = $categoryRepo->findOneByName('IMPRESSION');

   	} else {
   		$total = $orderRepo->totalAmount();
   		$orders = count($orderRepo->findAll());
   		$online = count($orderRepo->findBy([ "paymentType" => 0 ]));
   		$local = count($orderRepo->findBy([ "paymentType" => 1 ]));
      $delivery = count($orderRepo->findBy([ "paymentType" => 2 ]));
   		$cash = count($orderRepo->findBy([ "paymentMethod" => 0 ]));
   		$transcash = count($orderRepo->findBy([ "paymentMethod" => 1 ]));
   		$card = count($orderRepo->findBy([ "paymentMethod" => 2 ]));
   		$paypal = count($orderRepo->findBy([ "paymentMethod" => 3 ]));
   		$pcs = count($orderRepo->findBy([ "paymentMethod" => 4 ]));
   		$check = count($orderRepo->findBy([ "paymentMethod" => 5 ]));
   		$paysafecard = count($orderRepo->findBy([ "paymentMethod" => 6 ]));
   		$bank = count($orderRepo->findBy([ "paymentMethod" => 7 ]));
   		$waiting = count($orderRepo->findBy([ "status" => 0 ]));
   		$partial = count($orderRepo->findBy([ "status" => 1 ]));
   		$paid = count($orderRepo->findBy([ "status" => 2 ]));
   		$refund = count($orderRepo->findBy([ "status" => 3 ]));
   		$bestProducts = $orderRepo->findBestProducts();
   		$bestCategories = $orderRepo->findBestCategories();
   		$bestCustomers = $orderRepo->findBestCustomers();
   		$expenses = $fluxRepo->findByMonth(1);
   		$results = $orderRepo->findByMonth();
   		$profit = 0;

   		foreach ($results as $result) {
   			$profit = $profit + ($result->getTotal() - $result->getShippingCost());
   			foreach ($result->getLineItems() as $line) {
   				$profit = $profit - ($line->getQuantity() * $line->getProduct()->getPurchasePrice());
   			}
   		}


   		$priceLists = $priceRepo->findPriceListName();
   		$category = $categoryRepo->findOneByName('IMPRESSION');

   		$now = new \DateTime('now', timezone_open('Europe/Paris'));
   		$nb = $now->format('d');

      // mois actuel
   		for ($i = 0; $i < $nb; $i++) {
   			$labels[] = $this->dateToFrench($now->format('d M'));
   			$amount = $orderRepo->totalAmountByDay($now->format('Y-m-d'));
   			$url = "/admin/orders?start=". $now->format('Y-m-d') . "&end=". $now->format('Y-m-d');
   			$now->modify('-1 day');

   			if ($amount[0]['total']) {
   				$amounts[] = [ 'total' => str_replace(",", ".", round($amount[0]['total'], 2)), 'url' => $url ];
   			} else {
   				$amounts[] = [ 'total' => 0, 'url' => $url ];
   			}
   		}
   		$array['labels'] = array_reverse($labels);
   		$array['amounts'] = array_reverse($amounts);
   	}

   	$cashflows = $fluxRepo->totalAmount(0);
   	$products = $productRepo->findBy(['archive' => false, 'digital' => false ]);
   	$stock = 0; 

   	foreach ($products as $product) {
   		$quantity = $stockRepo->findQuantityByProduct($product);
   		$listPrices = $priceRepo->findByProduct($product);
   		if ($listPrices && $quantity) {
   			$price = 0;
   			foreach ($listPrices as $list) {
   				$price = $price + $list['price'];
   			}
   			$price = $price / sizeof($listPrices);
   			$stock = $stock + ($quantity[0]['quantity'] * round($price, 2));
   		}
   	}

      // 12 derniers mois
   	$date1->modify('first day of this month')->setTime(0, 0, 0);
   	$year = $date1->format('Y');

   	for ($i = 0; $i < 12; $i++) {
   		$labels2[] = $this->dateToFrench($date1->format('M Y'));
   		$last = cal_days_in_month(CAL_GREGORIAN, $date1->format('m'), $date1->format('Y'));

   		$amount2 = $orderRepo->totalAmountByStartAndEnd($date1->format('Y-m-01'), $date1->format('Y-m') . "-" . $last . "");
   		if ($i == 0) {
   			$month = $amount2;
   			$annual = $amount2[0]['total'];
   		} elseif ($date1->format('Y') == $year) {
   			$annual = $annual + $amount2[0]['total'];
   		}

   		$url = "/admin/statistiques?start=". $date1->format('Y-m-01') . "&end=". $date1->format('Y-m') . "-" . $last . "";
   		$date1->modify('-1 month');

   		if ($amount2[0]['total']) {
   			$amounts2[] = [ 'total' => str_replace(",", ".", round($amount2[0]['total'], 2)), 'url' => $url ];
   		} else {
   			$amounts2[] = [ 'total' => 0, 'url' => $url ];
   		}
   	}


   	$notes = $noteRepo->findAll();
   	$ordersNote = $orderRepo->findByNotNote();

   	if ($notes) {
   		foreach ($notes as $note) {
   			foreach ($note->getTransactions() as $key => $transaction) {
   				if ($key == 0) {
   					$notPaid = $notPaid + $transaction->getAmount();
   				} elseif ($transaction->getInvoice()) {
   					$remaining = $transaction->getInvoice()->getTotal() - $transaction->getInvoice()->getPaid();
   					$notPaid = $notPaid + $remaining;
   				} elseif ($transaction->getAmount() > 0) {
   					$notPaid = $notPaid + $transaction->getAmount();
   				}
   			}
   		}
   	}

   	foreach ($ordersNote as $item) {
   		$notPaid = $notPaid + ($item->getTotal() - $item->getPaid());
   	}

   	if ($cashflows) {
   		$notPaid = $notPaid + $cashflows[0]['amount'];
   	}

   	$array2['labels'] = array_reverse($labels2);
   	$array2['amounts'] = array_reverse($amounts2);

   	return $this->render('admin/statistiques/index.html.twig', [
   		'start' => $start,
   		'end' => $end,
   		'now' => $now,
   		'note' => $note,
   		'profit' => $profit,
   		'stock' => $stock,
   		'orders' => $orders,
   		'annual' => $annual,
   		'month' => $month,
   		'total' => $total,
   		'expenses' => $expenses,
   		'cashflows' => $cashflows,
   		'online' => $online,
   		'local' => $local,
      'delivery' => $delivery,
   		'cash' => $cash,
   		'transcash' => $transcash,
   		'card' => $card,
   		'paypal' => $paypal,
   		'pcs' => $pcs,
   		'check' => $check,
   		'paysafecard' => $paysafecard,
   		'bank' => $bank,
   		'waiting' => $waiting,
   		'partial' => $partial,
   		'paid' => $paid,
   		'refund' => $refund,
   		'bestProducts' => $bestProducts,
   		'bestCategories' => $bestCategories,
   		'bestCustomers' => $bestCustomers,
   		'array' => $array,
   		'array2' => $array2,
   		'notPaid' => $notPaid,
   	]);
   }


   /**
   * Permet d'afficher la valeur des stocks
   *
   * @Route("/admin/statistiques/stocks", name="admin_statistiques_stocks")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
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