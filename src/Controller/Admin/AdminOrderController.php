<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Entity\OrderHistory;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Admin;
use App\Form\AdminOrderType;
use App\Service\Order\OrderService;
use App\Service\Order\OrderExportService;
use App\Repository\AdminRepository;
use App\Repository\VariantRepository;
use App\Repository\OrderHistoryRepository;
use App\Repository\LineItemRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Repository\TransactionRepository;
use App\Repository\OrderRepository;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use App\Enum\OrderStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderService $orderService,
        private OrderExportService $orderExportService,
        private OrderRepository $orderRepository
    ) {
    }

    private function getAdmin(): Admin
    {
        /** @var Admin */
        return $this->getUser();
    }

    #[Route('/admin/orders', name: 'admin_order_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        if (!$start || !$end) {
            $start = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $end = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        } else {
            $start = new \DateTime($start);
            $end = new \DateTime($end);
        }

        $result = $this->orderService->getTotalsByDateRange($start, $end);

        return $this->render('admin/order/index.html.twig', [
            'search' => '',
            'orders' => $result['orders'],
            'alreadyPaid' => $result['alreadyPaid'],
            'total' => $result['total'],
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/orders/status', name: 'admin_order_status')]
    #[IsGranted('ROLE_ADMIN')]
    public function status(Request $request): Response
    {
        $statusValue = $request->query->get('status');
        $status = OrderStatus::from((int)$statusValue);
        $result = $this->orderService->getOrdersByStatus($status->value);
        
        return $this->render('admin/order/index.html.twig', [
            'search' => '',
            'orders' => $result['orders'],
            'total' => $result['total'],
            'alreadyPaid' => $result['alreadyPaid'],
            'start' => (new \DateTime())->format('Y-m-d'),
            'end' => (new \DateTime())->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/orders/waiting', name: 'admin_order_waiting')]
    #[IsGranted('ROLE_ADMIN')]
    public function waiting(): Response
    {
        $result = $this->orderService->getOrdersByStatus(OrderStatus::WAITING->value);
        
        return $this->render('admin/order/index.html.twig', [
            'search' => '',
            'orders' => $result['orders'],
            'total' => $result['total'],
            'alreadyPaid' => $result['alreadyPaid'],
            'start' => (new \DateTime())->format('Y-m-d'),
            'end' => (new \DateTime())->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/orders/paymentType', name: 'admin_order_paymentType')]
    #[IsGranted('ROLE_ADMIN')]
    public function paymentType(Request $request): Response
    {
        $typeValue = $request->query->get('paymentType');
        $type = PaymentType::from((int)$typeValue);
        $orders = $this->orderRepository->findByPaymentType($type->value);
        
        return $this->render('admin/order/index.html.twig', 
            $this->orderService->prepareIndexViewData($orders)
        );
    }

    #[Route('/admin/orders/paymentMethod', name: 'admin_order_paymentMethod')]
    #[IsGranted('ROLE_ADMIN')]
    public function paymentMethod(Request $request): Response
    {
        $methodValue = $request->query->get('paymentMethod');
        $method = PaymentMethod::from((int)$methodValue);
        $orders = $this->orderRepository->findByPaymentMethod($method->value);
        
        return $this->render('admin/order/index.html.twig', 
            $this->orderService->prepareIndexViewData($orders)
        );
    }

    #[Route('/admin/orders/expedition', name: 'admin_order_expedition')]
    #[IsGranted('ROLE_ADMIN')]
    public function expedition(): Response
    {
        $orders = $this->orderRepository->findByExpedition();
        
        return $this->render('admin/order/index.html.twig', 
            $this->orderService->prepareIndexViewData($orders)
        );
    }

    #[Route('/admin/orders/impayee', name: 'admin_order_impayee')]
    #[IsGranted('ROLE_ADMIN')]
    public function impayee(): Response
    {
        $orders = $this->orderRepository->findByImpayee();
        
        return $this->render('admin/order/index.html.twig', 
            $this->orderService->prepareIndexViewData($orders)
        );
    }

    #[Route('/admin/orders/customers', name: 'admin_order_customers')]
    #[IsGranted('ROLE_ADMIN')]
    public function customers(Request $request, OrderRepository $orderRepo, LineItemRepository $lineItemRepo): Response
    {
        $customers = $orderRepo->groupByCustomers();
        $array = [];

        foreach ($customers as $customer) {
            $orders = $orderRepo->findByFirstname($customer['firstname']);
            $array[] = [
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'number' => $customer['number'],
                'email' => $customer['email'],
                'orders' => $orders
            ];
        }

        return $this->render('admin/order/customers.html.twig', [
            'array' => $array,
        ]);
    }

    #[Route('/admin/orders/new', name: 'admin_order_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(ProductRepository $productRepository, VariantRepository $variantRepo, Request $request, EntityManagerInterface $manager, StockListRepository $stockRepo, AdminRepository $adminRepo): Response
    {
        $order = new Order();
        $variants = $variantRepo->findBy(['archive' => false], ['title' => "ASC"]);
        $products = $productRepository->findBy(['archive' => false], ['title' => "ASC"]);
        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $stockList = $request->request->all('stockListId');
            $priceList = $request->request->all('priceList');
            $variantId = $request->request->all('variantId');
            $title = $request->request->all('title');
            $quantity = $request->request->all('quantity');
            $price = $request->request->all('price');
            
            if ($variantId) {
                for ($i = 0; $i < count($variantId); $i++) {
                    $variant = $variantRepo->findOneById($variantId[$i]);

                    if ($variant) {
                        $product = $variant->getProduct();
                        $stock = $stockRepo->findOneBy(['name' => $stockList[$i], 'product' => $product ]);

                        if ($stock) {
                            $item = new LineItem();
                            $item->setTitle($title[$i]);
                            $item->setQuantity($quantity[$i]);
                            $item->setPrice($price[$i]);
                            $item->setVariant($variant);
                            $item->setProduct($product);
                            $item->setPriceList($priceList[$i]);
                            $item->setOrder($order);
                            $item->setStock($stock);

                            $manager->persist($item);

                            if ($stock->getQuantity() - $quantity[$i] < 0) {
                                $variable = abs($stock->getQuantity() - $quantity[$i]);
                                $this->addFlash(
                                    'error',
                                    "Il manque {$variable} {$variant->getTitle()} dans le stock à {$stock->getName()} !"
                                );
                            }
                            
                            $order->addLineItem($item);
                            $stock->setQuantity($stock->getQuantity() - $quantity[$i]);

                            /** @var Admin */
                            $admin = $this->getAdmin();
                            $history = new OrderHistory();
                            $history->setTitle("Le produit '{$variant->getTitle()}' a été ajouté en '{$quantity[$i]}' exemplaire(s) pour '{$price[$i]}€'");
                            $history->setInvoice($order);
                            $history->setAdmin($admin);
                            $manager->persist($history);
                            $manager->flush();
                        }
                    }
                }
            }

            if (!$order->getLineItems()->toArray()) {
                $this->addFlash(
                    'error',
                    "Il faut ajouter un produit pour créer une commande !"
                );

                return $this->redirectToRoute('admin_order_new');
            }

            if ($order->getTotal() < $order->getPaid()) {
                $order->setStatus(3);
            } elseif ($order->getTotal() == $order->getPaid()) {
                $order->setStatus(2);
            } elseif ($order->getPaid() != 0) {
                $order->setStatus(1);
            } else {
                $order->setStatus(0);
            }

            /** @var Admin */
            $admin = $this->getAdmin();
            $order->setAdmin($admin);
            $manager->persist($order);
            $manager->flush();

            $this->addFlash(
                'success',
                "Une nouvelle commande à été ajouté !"
            );

            return $this->redirectToRoute('admin_order_index');
        }

        return $this->render('admin/order/new.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'variants' => $variants,
        ]);
    }

    #[Route('/admin/order/{id}/edit', name: 'admin_order_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Order $order, Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                "La commande a été modifiée !"
            );

            return $this->redirectToRoute('admin_order_index');
        }

        $products = $productRepository->findBy(['archive' => false], ['title' => "ASC"]);

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'products' => $products
        ]);
    }

    #[Route('/admin/orders/print', name: 'admin_order_print')]
    #[IsGranted('ROLE_ADMIN')]
    public function print(OrderRepository $orderRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $search = $request->query->get('search');

        if ($search) {
            $orders = $orderRepo->search($search);
        } else if ($start && $end) {
            $orders = $orderRepo->findByStartAndEnd($start, $end);
        } else {
            $orders = $orderRepo->findBy([], ['createdAt' => "DESC"]);
        }

        return $this->render('admin/order/print.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/admin/lineitem/delete/{id}', name: 'admin_lineitem_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteLineitem(LineItem $lineItem, StockListRepository $stockRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $stock = $lineItem->getStock();
        if ($stock) {
            $quantity = $lineItem->getQuantity();
            $stock->setQuantity($stock->getQuantity() + $quantity);
            $order = $lineItem->getOrder();
            $order->setTotal($order->getTotal() - $lineItem->getPrice());
        }

        $admin = $this->getAdmin();
        $history = new OrderHistory();
        $history->setTitle("Le produit '{$lineItem->getTitle()}' en '{$lineItem->getQuantity()}' exemplaire(s) pour '{$lineItem->getPrice()}€' a été supprimé");
        $history->setInvoice($lineItem->getOrder());
        $history->setAdmin($admin);
        $manager->persist($history);

        $manager->remove($lineItem);
        $manager->flush();

        return new JsonResponse([
            'message' => 'success'
        ]);
    }

    #[Route('/admin/orders/delete/{id}', name: 'admin_order_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOrder(Order $order, StockListRepository $stockRepo, EntityManagerInterface $manager, TransactionRepository $transactionRepo): Response
    {
        $lineItems = $order->getLineItems();
        
        if ($lineItems) {
            foreach ($lineItems as $lineItem) {
                $stock = $lineItem->getStock();
                if ($stock) {
                    $quantity = $lineItem->getQuantity();
                    $stock->setQuantity($stock->getQuantity() + $quantity);
                }
            }
        }

        $transaction = $transactionRepo->findOneByInvoice($order);
        if ($transaction) {
            $manager->remove($transaction);
        }

        $manager->remove($order);
        $manager->flush();

        $this->addFlash(
            'success',
            "La commande a été supprimée !"
        );

        return $this->redirectToRoute('admin_order_index');
    }

    #[Route('/admin/orders/export/{id}', name: 'admin_order_export')]
    #[IsGranted('ROLE_ADMIN')]
    public function exportOrder(Order $order, Request $request): Response
    {
        $format = $request->query->get('format', 'pdf');
        return $this->orderExportService->exportOrder($order, $format);
    }

    #[Route('/admin/orders/customer/autocomplete', name: 'admin_order_customer')]
    #[IsGranted('ROLE_ADMIN')]
    public function customer(Request $request, OrderRepository $orderRepo): Response
    {
        $keyword = $request->query->get('keyword'); $array = [];
        $orders = $orderRepo->searchCustomer($keyword);

        if ($orders) {
            foreach ($orders as $key => $order) {
                $array[] = $order['firtname'];
            }
        }

        return $this->json($array, 200);
    }

    #[Route('/admin/orders/history/{id}', name: 'admin_order_history')]
    #[IsGranted('ROLE_ADMIN')]
    public function history(Order $order, Request $request, OrderRepository $orderRepo, LineItemRepository $lineItemRepo): Response
    {
        return $this->render('admin/order/history.html.twig', [
            'order' => $order,
        ]);
    }

    public static function dateToFrench(string $date): string
    {
        $french_months = ["Janv.", "Févr.", "Mars", "Avr.", "Mai", "Juin", "Juil.", "Aoùt", "Sept.", "Oct.", "Nov.", "Déc."];
        $english_months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        
        return str_replace($english_months, $french_months, $date);
    }
}