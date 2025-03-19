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
    #[Route('/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        EntityManagerInterface $manager,
        UserRepository $usersRepo,
        ResellerRepository $resellerRepo,
        ProductRepository $productRepo,
        OrderRepository $orderRepo,
        TransactionRepository $transactionRepo,
        TaskRepository $taskRepo,
        FluxRepository $fluxRepo,
        NoteRepository $noteRepo
    ): Response {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('admin_order_index');
        }

        $now = new \DateTime('now', timezone_open('Europe/Paris'));
        $users = $usersRepo->findUsersLimit(10);
        $orders = count($orderRepo->findOrderNotComplete());
        $totalResellers = count($resellerRepo->findAll());
        $totalUsers = count($usersRepo->findAll());
        $tasks = count($taskRepo->findBy(['complete' => false]));

        $orderNotPaid = $orderRepo->findByImpayee();
        $nbOrders = $orderRepo->findByStartAndEnd($now->format('Y-m-d'), $now->format('Y-m-d'));
        $todayAmount = $orderRepo->totalAmountByStartAndEnd($now->format('Y-m-d'), $now->format('Y-m-d'));
        $notPaid = 0;
        $cashflows = $fluxRepo->totalAmount(0);
        $notes = $noteRepo->findAll();
        $ordersNote = $orderRepo->findByNotNote();

        if ($notes) {
            foreach ($notes as $note) {
                foreach ($note->getTransactions() as $key => $transaction) {
                    if ($key === 0) {
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

        return $this->render('admin/dashboard/index.html.twig', [
            'users' => $users,
            'orders' => $orders,
            'tasks' => $tasks,
            'nbOrders' => $nbOrders,
            'notPaid' => $notPaid,
            'todayAmount' => $todayAmount,
            'totalUsers' => $totalUsers,
            'totalResellers' => $totalResellers,
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
