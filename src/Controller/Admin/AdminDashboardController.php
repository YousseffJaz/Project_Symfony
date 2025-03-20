<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\LineItem;
use App\Repository\OrderRepository;
use App\Repository\NoteRepository;
use App\Repository\ProductRepository;
use App\Repository\TaskRepository;
use App\Repository\NotificationRepository;
use App\Repository\VariantRepository;
use App\Repository\AdminRepository;
use App\Repository\FluxRepository;
use App\Repository\ResellerRepository;
use App\Repository\TransactionRepository;
use App\Repository\InfoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepo,
        OrderRepository $orderRepo,
        ProductRepository $productRepo,
        TaskRepository $taskRepo
    ): Response {
        // Statistiques utilisateurs
        $totalUsers = count($userRepo->findAll());

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
        $todayOrdersCount = count($todayOrders);
        $todayOrdersTotal = 0;
        foreach ($todayOrders as $order) {
            $todayOrdersTotal += $order->getTotal();
        }

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
        $monthlyRevenue = 0;
        foreach ($monthOrders as $order) {
            $monthlyRevenue += $order->getTotal();
        }

        // Produits en rupture de stock
        $lowStockProducts = count($productRepo->findBy(['alert' => true]));

        // Tâches en attente
        $pendingTasks = count($taskRepo->findBy(['complete' => false]));

        return $this->render('admin/dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'todayOrdersCount' => $todayOrdersCount,
            'todayOrdersTotal' => $todayOrdersTotal,
            'pendingOrders' => $pendingOrders,
            'processingOrders' => $processingOrders,
            'deliveredOrders' => $deliveredOrders,
            'canceledOrders' => $canceledOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'lowStockProducts' => $lowStockProducts,
            'pendingTasks' => $pendingTasks,
        ]);
    }

    #[Route('/search/global', name: 'admin_search_global')]
    #[IsGranted('ROLE_ADMIN')]
    public function search(Request $request, OrderRepository $orderRepo, UserRepository $userRepo): Response
    {
        $search = $request->request->get('search');
        $array = [];
        $orders = $orderRepo->search($search);
        $total = 0;
        $alreadyPaid = 0;

        if ($orders) {
            foreach ($orders as $order) {
                $total = $total + $order->getTotal();
                $alreadyPaid = $alreadyPaid + $order->getPaid();
            }
        }

        $start = new \DateTime('now', timezone_open('Europe/Paris'));
        $end = new \DateTime('now', timezone_open('Europe/Paris'));
        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        return $this->render('admin/order/index.html.twig', [
            'search' => $search,
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid,
            'start' => $start,
            'end' => $end,
        ]);
    }

    #[Route('/notification/all', name: 'admin_notif_all')]
    public function notification(
        Request $request,
        EntityManagerInterface $manager,
        NotificationRepository $notifRepo
    ): Response {
        $sound = false;
        
        if (!$this->getUser()) {
            return $this->json($sound, 200);
        }

        $notifs = $notifRepo->findBy(['admin' => $this->getUser(), 'seen' => false]);

        if ($notifs) {
            foreach ($notifs as $notif) {
                if (!$notif->getSeen()) {
                    $sound = true;
                    $notif->setSeen(true);
                    $manager->flush();
                }
            }
        }

        return $this->json($sound, 200);
    }
}
