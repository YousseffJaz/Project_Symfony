<?php

namespace App\Controller\Admin;

use App\Entity\OrderHistory;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Admin;
use App\Form\AdminOrderType;
use App\Service\Order\OrderService;
use App\Service\Order\OrderExportService;
use App\Repository\AdminRepository;
use App\Repository\VariantRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\OrderRepository;
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
            // Par défaut, on prend l'année en cours
            $currentYear = (new \DateTime())->format('Y');
            $start = new \DateTime($currentYear . '-01-01');
            $start->setTime(0, 0, 0);
            $end = new \DateTime($currentYear . '-12-31');
            $end->setTime(23, 59, 59);
        } else {
            $start = new \DateTime($start);
            $start->setTime(0, 0, 0);
            $end = new \DateTime($end);
            $end->setTime(23, 59, 59);
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

    #[Route('/admin/orders/filter/{type}/{value}', name: 'admin_order_filter')]
    #[IsGranted('ROLE_ADMIN')]
    public function filter(string $type, ?string $value = null): Response
    {
        $result = $this->orderService->getFilteredOrders($type, $value);
        
        return $this->render('admin/order/index.html.twig', 
            $this->orderService->prepareIndexViewData($result['orders'])
        );
    }

    #[Route('/admin/orders/customers', name: 'admin_order_customers')]
    #[IsGranted('ROLE_ADMIN')]
    public function customers(): Response
    {
        return $this->render('admin/order/customers.html.twig', [
            'array' => $this->orderService->getCustomerOrders(),
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
                $order->setStatus(OrderStatus::REFUND->value);
            } elseif ($order->getTotal() == $order->getPaid()) {
                $order->setStatus(OrderStatus::PAID->value);
            } elseif ($order->getPaid() != 0) {
                $order->setStatus(OrderStatus::PARTIAL->value);
            } else {
                $order->setStatus(OrderStatus::WAITING->value);
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
    public function print(Request $request): Response
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $search = $request->query->get('search');

        if ($search) {
            $orders = $this->orderRepository->search($search);
        } else if ($start && $end) {
            // Ajouter les heures pour avoir la journée complète
            $startDate = new \DateTime($start);
            $startDate->setTime(0, 0, 0);
            $endDate = new \DateTime($end);
            $endDate->setTime(23, 59, 59);
            
            $orders = $this->orderRepository->findByStartAndEnd(
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            );
        } else {
            // Default to current year if no dates specified
            $currentYear = (new \DateTime())->format('Y');
            $yearStart = new \DateTime($currentYear . '-01-01');
            $yearStart->setTime(0, 0, 0);
            $yearEnd = new \DateTime($currentYear . '-12-31');
            $yearEnd->setTime(23, 59, 59);
            
            $orders = $this->orderRepository->findByStartAndEnd(
                $yearStart->format('Y-m-d'),
                $yearEnd->format('Y-m-d')
            );
        }

        return $this->render('admin/order/print.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/admin/lineitem/delete/{id}', name: 'admin_lineitem_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteLineitem(LineItem $lineItem): Response
    {
        $this->orderService->deleteLineItem($lineItem, $this->getAdmin());

        return new JsonResponse([
            'message' => 'success'
        ]);
    }

    #[Route('/admin/orders/delete/{id}', name: 'admin_order_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOrder(Order $order): Response
    {
        $this->orderService->deleteOrder($order);

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
    public function customer(Request $request): Response
    {
        $keyword = $request->query->get('keyword');
        $orders = $this->orderRepository->searchCustomer($keyword);
        $array = [];

        if ($orders) {
            foreach ($orders as $order) {
                $array[] = $order['firtname'];
            }
        }

        return $this->json($array, 200);
    }

    #[Route('/admin/orders/history/{id}', name: 'admin_order_history')]
    #[IsGranted('ROLE_ADMIN')]
    public function history(Order $order): Response
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