<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\NotificationRepository;
use App\Repository\AdminRepository;
use App\Service\Order\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        OrderRepository $orderRepo,
        ProductRepository $productRepo,
        AdminRepository $adminRepo
    ): Response {
        // Statistiques commandes
        $today = new \DateTime('now');
        $todayStart = clone $today;
        $todayStart->setTime(0, 0, 0);
        $todayEnd = clone $today;
        $todayEnd->setTime(23, 59, 59);

        // Commandes du jour
        $todayOrders = $orderRepo->findByStartAndEnd(
            $todayStart->format('Y-m-d'),
            $todayEnd->format('Y-m-d')
        );
        $todayStats = $this->orderService->calculateTotals($todayOrders);
        $todayOrdersCount = count($todayOrders);

        // Commandes en attente
        $pendingOrders = count($orderRepo->findBy(['status' => 0]));
        
        // Commandes en cours
        $processingOrders = count($orderRepo->findBy(['status' => 1]));
        
        // Commandes livrées
        $deliveredOrders = count($orderRepo->findBy(['status' => 2]));
        
        // Commandes annulées
        $canceledOrders = count($orderRepo->findBy(['status' => 3]));

        // Chiffre d'affaires du mois
        $monthStart = new \DateTime('first day of this month');
        $monthEnd = new \DateTime('last day of this month');
        $monthOrders = $orderRepo->findByStartAndEnd(
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d')
        );
        $monthStats = $this->orderService->calculateTotals($monthOrders);
        $monthlyRevenue = $monthStats['total'];

        // Produits en rupture de stock
        $lowStockProducts = count($productRepo->findProductAlmostSoldOut());

        $totalOrders = count($orderRepo->findAll());
        $totalProducts = count($productRepo->findAll());
        $totalAdmins = count($adminRepo->findAll());

        return $this->render('admin/dashboard/index.html.twig', [
            'todayOrdersCount' => $todayOrdersCount,
            'todayOrdersTotal' => $todayStats['total'],
            'pendingOrders' => $pendingOrders,
            'processingOrders' => $processingOrders,
            'deliveredOrders' => $deliveredOrders,
            'canceledOrders' => $canceledOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'lowStockProducts' => $lowStockProducts,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalAdmins' => $totalAdmins,
        ]);
    }

    #[Route('/search/global', name: 'admin_search_global')]
    #[IsGranted('ROLE_ADMIN')]
    public function search(Request $request, OrderRepository $orderRepo): Response
    {
        $search = $request->request->get('search');
        $orders = $orderRepo->search($search);
        
        return $this->render('admin/order/index.html.twig',
            $this->orderService->prepareIndexViewData($orders)
        );
    }
}
