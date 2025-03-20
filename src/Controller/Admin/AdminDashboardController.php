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
    public function index(UserRepository $userRepo): Response
    {
        $totalUsers = count($userRepo->findAll());

        return $this->render('admin/dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
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
