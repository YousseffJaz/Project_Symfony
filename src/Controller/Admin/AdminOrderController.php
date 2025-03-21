<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Entity\OrderHistory;
use App\Entity\Notification;
use App\Entity\Upload;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Admin;
use App\Form\AdminOrderType;
use App\Service\Order\OrderService;
use App\Service\Order\OrderExportService;
use App\Service\Order\OrderUploadService;
use App\Repository\AdminRepository;
use App\Repository\VariantRepository;
use App\Repository\OrderHistoryRepository;
use App\Repository\UploadRepository;
use App\Repository\LineItemRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\PriceListRepository;
use App\Repository\TransactionRepository;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

class AdminOrderController extends AbstractController
{
    public function __construct(
        private Security $security,
        private OrderService $orderService,
        private OrderExportService $orderExportService,
        private OrderUploadService $orderUploadService
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

        if ($this->security->isGranted('ROLE_LIVREUR')) {
            $orders = $this->orderService->getOrdersByDeliveryAndUser($this->getAdmin());
            return $this->render('admin/order/index.html.twig', [
                'search' => '',
                'orders' => $orders,
                'alreadyPaid' => "0,00€",
                'total' => "",
                'start' => "",
                'end' => "",
            ]);
        }

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
        $status = $request->query->get('status');
        $result = $this->orderService->getOrdersByStatus($status);
        
        return $this->render('admin/order/index.html.twig', [
            'search' => '',
            'orders' => $result['orders'],
            'total' => $result['total'],
            'alreadyPaid' => $result['alreadyPaid'],
            'start' => (new \DateTime())->format('Y-m-d'),
            'end' => (new \DateTime())->format('Y-m-d'),
        ]);
    }

    #[Route('/admin/orders/waitting', name: 'admin_order_waitting')]
    #[IsGranted('ROLE_ADMIN')]
    public function waitting(): Response
    {
        $result = $this->orderService->getWaitingOrders();
        
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
    public function paymentType(Request $request, OrderRepository $orderRepo): Response
    {
        $paymentType = $request->query->get('paymentType');
        $orders = $orderRepo->findByPaymentType($paymentType);
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
            'search' => '',
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid,
            'start' => $start,
            'end' => $end,
        ]);
    }

    #[Route('/admin/orders/paymentMethod', name: 'admin_order_paymentMethod')]
    #[IsGranted('ROLE_ADMIN')]
    public function paymentMethod(Request $request, OrderRepository $orderRepo): Response
    {
        $paymentMethod = $request->query->get('paymentMethod');
        $orders = $orderRepo->findByPaymentMethod($paymentMethod);
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
            'search' => '',
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid,
            'start' => $start,
            'end' => $end,
        ]);
    }

    #[Route('/admin/orders/expedition', name: 'admin_order_expedition')]
    #[IsGranted('ROLE_ADMIN')]
    public function expedition(Request $request, OrderRepository $orderRepo): Response
    {
        $orders = $orderRepo->findByExpedition();
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
            'search' => '',
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid,
            'start' => $start,
            'end' => $end,
        ]);
    }

    #[Route('/admin/orders/impayee', name: 'admin_order_impayee')]
    #[IsGranted('ROLE_ADMIN')]
    public function impayee(Request $request, OrderRepository $orderRepo): Response
    {
        $orders = $orderRepo->findByImpayee();
        $total = 0;
        $alreadyPaid = 0;
        $remaining = 0;

        if ($orders) {
            foreach ($orders as $order) {
                $remaining = $remaining + ($order->getTotal() - $order->getPaid());
                $total = $total + $order->getTotal();
                $alreadyPaid = $alreadyPaid + $order->getPaid();
            }   
        }

        $start = new \DateTime('now', timezone_open('Europe/Paris'));
        $end = new \DateTime('now', timezone_open('Europe/Paris'));
        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        return $this->render('admin/order/index.html.twig', [
            'search' => '',
            'orders' => $orders,
            'total' => $total,
            'start' => $start,
            'alreadyPaid' => $alreadyPaid,
            'end' => $end,
        ]);
    }

    #[Route('/admin/orders/livraison', name: 'admin_order_livraison')]
    #[IsGranted('ROLE_ADMIN')]
    public function livraison(Request $request, OrderRepository $orderRepo): Response
    {
        $orders = $orderRepo->findByLivraison();
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
            'search' => '',
            'orders' => $orders,
            'total' => $total,
            'alreadyPaid' => $alreadyPaid,
            'start' => $start,
            'end' => $end,
        ]);
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
                            $item->setOrderItem($order);
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

            if ($order->getNote2()) {
                $note = $order->getNote2();
                $transaction = new Transaction();
                $transaction->setAmount($order->getTotal());
                $transaction->setNote($note);
                $transaction->setInvoice($order);
                $manager->persist($transaction);
            }

            if ($order->getOrderStatus() == 4) {
                $admins = $adminRepo->findBy(['role' => 'ROLE_LIVREUR']);

                if ($admins) {
                    foreach ($admins as $admin) {
                        $notif = new Notification();
                        $notif->setAdmin($admin);
                        $notif->setInvoice($order);     
                        $manager->persist($notif);              
                    }
                }
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

    #[Route('/admin/orders/edit/{id}', name: 'admin_order_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Order $order, ProductRepository $productRepository, VariantRepository $variantRepo, Request $request, EntityManagerInterface $manager, TransactionRepository $transactionRepo, StockListRepository $stockRepo, AdminRepository $adminRepo): Response
    {
        $order2 = clone $order;
        $form = $this->createForm(AdminOrderType::class, $order);
        $products = $productRepository->findBy(['archive' => false], ['title' => "ASC"]);
        $variants = $variantRepo->findBy(['archive' => false], ['title' => "ASC"]);
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
                            $item->setOrderItem($order);
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
                $manager->flush();

                $this->addFlash(
                    'error',
                    "Il faut ajouter un produit !"
                );

                return $this->redirectToRoute('admin_order_edit', [ 'id' => $order->getId() ]);
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

            $transaction = $transactionRepo->findOneByInvoice($order);
            $note = $order->getNote2();

            if ($note) {
                if (!$transaction) {
                    $transaction = new Transaction();
                    $transaction->setAmount($order->getTotal());
                    $transaction->setNote($note);
                    $transaction->setInvoice($order);
                    $manager->persist($transaction);
                } else {
                    if ($transaction->getAmount() != $order->getTotal()) {
                        $transaction->setAmount($order->getTotal());
                    }
                }
            } else {
                if ($transaction) {
                    $manager->remove($transaction);
                }
            }

            // history
            if ($order2->getFirstname() != $order->getFirstname()) {
                $history = new OrderHistory();
                $history->setTitle("Le prénom '{$order2->getFirstname()}' a été modifié par '{$order->getFirstname()}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getLastname() != $order->getLastname()) {
                $history = new OrderHistory();
                $history->setTitle("Le nom '{$order2->getLastname()}' a été modifié par '{$order->getLastname()}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getAddress() != $order->getAddress()) {
                $history = new OrderHistory();
                $history->setTitle("L'adresse '{$order2->getAddress()}' a été modifié par '{$order->getAddress()}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getPhone() != $order->getPhone()) {
                $history = new OrderHistory();
                $history->setTitle("Le numéro de téléphone '{$order2->getPhone()}' a été modifié par '{$order->getPhone()}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getShippingCost() != $order->getShippingCost()) {
                $history = new OrderHistory();
                $history->setTitle("Les frais d'expédition '{$order2->getShippingCost()}€' ont été modifié par '{$order->getShippingCost()}€'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getDiscount() != $order->getDiscount()) {
                $history = new OrderHistory();
                $history->setTitle("La réduction de '{$order2->getDiscount()}%' a été modifié par '{$order->getDiscount()}%'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getPaid() != $order->getPaid()) {
                $history = new OrderHistory();
                $history->setTitle("Le montant payé de '{$order2->getPaid()}€' a été modifié par '{$order->getPaid()}€'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getNote() != $order->getNote()) {
                $history = new OrderHistory();
                $history->setTitle("Le commentaire '{$order2->getNote()}' a été modifié par '{$order->getNote()}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getStatus() != $order->getStatus()) {
                $status1 = "";
                $status2 = "";

                if ($order2->getStatus() == 0) {
                    $status1 = "En attente de paiement";
                } elseif ($order2->getStatus() == 1) {
                    $status1 = "Paiement partiel";
                } elseif ($order2->getStatus() == 2) {
                    $status1 = "Payé";
                } elseif ($order2->getStatus() == 3) {
                    $status1 = "Trop-perçu";
                }

                if ($order->getStatus() == 0) {
                    $status2 = "En attente de paiement";
                } elseif ($order->getStatus() == 1) {
                    $status2 = "Paiement partiel";
                } elseif ($order->getStatus() == 2) {
                    $status2 = "Payé";
                } elseif ($order->getStatus() == 3) {
                    $status2 = "Trop-perçu";
                }

                $history = new OrderHistory();
                $history->setTitle("Le statut du paiement '{$status1}' a été modifié par '{$status2}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getOrderStatus() != $order->getOrderStatus()) {
                $status1 = ""; $status2 = "";

                if ($order2->getOrderStatus() == 4) {
                    $status1 = "Commande à livrer";
                } elseif ($order2->getOrderStatus() == 3) {
                    $status1 = "Commande terminée";
                } else {
                    $status1 = "Commande à expédier";
                }


                if ($order->getOrderStatus() == 4) {
                    $status2 = "Commande à livrer";
                } elseif ($order->getOrderStatus() == 3) {
                    $status2 = "Commande terminée";
                } else {
                    $status2 = "Commande à expédier";
                }

                
                $history = new OrderHistory();
                $history->setTitle("Le statut de la commande '{$status1}' a été modifié par '{$status2}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            // design à réaliser --> commande à imprimer pour admin
            if ($order2->getOrderStatus() == 2 && $order->getOrderStatus() == 4) {
                $admins = $adminRepo->findBy(['role' => 'ROLE_SUPER_ADMIN']);

                if ($admins) {
                    foreach ($admins as $admin) {
                        $notif = new Notification();
                        $notif->setAdmin($admin);
                        $notif->setInvoice($order);     
                        $manager->persist($notif);              
                    }
                }
            }

            if ($order2->getPaymentType() != $order->getPaymentType()) {
                $status1 = ""; $status2 = "";

                if ($order2->getPaymentType() == 0) {
                    $status1 = "Internet";
                } elseif ($order2->getPaymentType() == 1) {
                    $status1 = "Physique";
                }

                if ($order->getPaymentType() == 0) {
                    $status2 = "Internet";
                } elseif ($order->getPaymentType() == 1) {
                    $status2 = "Physique";
                }
                
                $history = new OrderHistory();
                $history->setTitle("Le type de paiement '{$status1}' a été modifié par '{$status2}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if ($order2->getPaymentMethod() != $order->getPaymentMethod()) {
                $status1 = "";
                $status2 = "";

                if ($order2->getPaymentType() == 0) {
                    $status1 = "Espèce";
                } elseif ($order2->getPaymentType() == 1) {
                    $status1 = "Transcash";
                } elseif ($order2->getPaymentType() == 2) {
                    $status1 = "Carte bancaire";
                } elseif ($order2->getPaymentType() == 3) {
                    $status1 = "Paypal";
                } elseif ($order2->getPaymentType() == 4) {
                    $status1 = "PCS";
                } elseif ($order2->getPaymentType() == 5) {
                    $status1 = "Chèque";
                } elseif ($order2->getPaymentType() == 6) {
                    $status1 = "Paysafecard";
                } elseif ($order2->getPaymentType() == 7) {
                    $status1 = "Virement bancaire";
                }

                if ($order->getPaymentType() == 0) {
                    $status2 = "Espèce";
                } elseif ($order->getPaymentType() == 1) {
                    $status2 = "Transcash";
                } elseif ($order->getPaymentType() == 2) {
                    $status2 = "Carte bancaire";
                } elseif ($order->getPaymentType() == 3) {
                    $status2 = "Paypal";
                } elseif ($order->getPaymentType() == 4) {
                    $status2 = "PCS";
                } elseif ($order->getPaymentType() == 5) {
                    $status2 = "Chèque";
                } elseif ($order->getPaymentType() == 6) {
                    $status2 = "Paysafecard";
                } elseif ($order->getPaymentType() == 7) {
                    $status2 = "Virement bancaire";
                }

                
                $history = new OrderHistory();
                $history->setTitle("Le moyen de paiement '{$status1}' a été modifié par '{$status2}'");
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            if (sizeof($order2->getUploads()->toArray()) != sizeof($order->getUploads()->toArray())) {
                $history = new OrderHistory();
                if (sizeof($order2->getUploads()->toArray()) > sizeof($order->getUploads()->toArray())) {
                    $history->setTitle("Un ficher a été supprimé !");
                } else {
                    $history->setTitle("Un ficher a été ajouté !");
                }
                $history->setInvoice($order);
                $history->setAdmin($this->getAdmin());
                $manager->persist($history);
            }

            $manager->flush();

            $this->addFlash(
                'success',
                "La commande a été modifiée !"
            );

            return $this->redirectToRoute('admin_order_index');
        }

        return $this->render('admin/order/edit.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'products' => $products,
            'variants' => $variants,
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

    #[Route('/admin/order/upload', name: 'admin_order_upload')]
    #[IsGranted('ROLE_ADMIN')]
    public function upload(Request $request, OrderRepository $orderRepo): Response
    {
        $orderId = $request->request->get('orderId');
        $order = $orderRepo->find($orderId);
        
        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $file = $request->files->get('file');
        if (!$file) {
            throw new \InvalidArgumentException('Aucun fichier uploadé');
        }

        $upload = $this->orderUploadService->handleUpload($file, $order);

        return $this->json([
            'id' => $upload->getId(),
            'filename' => $upload->getFilename(),
            'name' => $upload->getName()
        ]);
    }

    #[Route('/admin/order/upload/delete', name: 'admin_order_upload_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUpload(Request $request, UploadRepository $repo): Response
    {
        $uploadId = $request->request->get('uploadId');
        $upload = $repo->find($uploadId);
        
        if (!$upload) {
            throw $this->createNotFoundException('Upload non trouvé');
        }

        $this->orderUploadService->deleteUpload($upload);

        return $this->json(['success' => true]);
    }

    #[Route('/admin/lineitem/delete/{id}', name: 'admin_lineitem_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteLineitem(LineItem $lineItem, StockListRepository $stockRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $stock = $stockRepo->findOneById($lineItem->getStock()?->getId());

        if ($stock) {
            $quantity = $lineItem->getQuantity();
            $stock->setQuantity($stock->getQuantity() + $quantity);
            $order = $lineItem->getOrderItem();
            $order->setTotal($order->getTotal() - $lineItem->getPrice());
        }

        /** @var Admin */
        $admin = $this->getAdmin();
        $history = new OrderHistory();
        $history->setTitle("Le produit '{$lineItem->getTitle()}' en '{$lineItem->getQuantity()}' exemplaire(s) pour '{$lineItem->getPrice()}€' a été supprimé");
        $history->setInvoice($lineItem->getOrderItem());
        $history->setAdmin($admin);
        $manager->persist($history);

        $manager->remove($lineItem);            
        $manager->flush();

        return $this->json(true);
    }

    #[Route('/admin/orders/delete/{id}', name: 'admin_order_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOrder(Order $order, StockListRepository $stockRepo, EntityManagerInterface $manager, TransactionRepository $transactionRepo, NoteRepository $noteRepo): Response
    {
        $items = $order->getLineItems();
        $histories = $order->getOrderHistories();
        $notifs = $order->getNotifications();

        if ($items) {
            foreach ($items as $item) {
                $stock = $stockRepo->findOneById($item->getStock()?->getId());

                if ($stock) {
                    $quantity = $item->getQuantity();
                    $stock->setQuantity($stock->getQuantity() + $quantity);
                }

                $manager->remove($item);            
            }  
        }

        if ($notifs) {
            foreach ($notifs as $notif) {
                $manager->remove($notif);            
            }     
        }

        if ($histories) {
            foreach ($histories as $history) {
                $manager->remove($history);            
            }     
        }

        if ($order->getNote2()) {
            $transaction = $transactionRepo->findOneByInvoice($order);
            if ($transaction) {
                $manager->remove($transaction);
            }
        }

        $manager->remove($order);
        $manager->flush();

        $this->addFlash(
            'success',
            "La commande a été supprimée !"
        );

        return $this->redirectToRoute("admin_order_index");
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

    #[Route('/admin/orders/histories', name: 'admin_order_histories')]
    #[IsGranted('ROLE_ADMIN')]
    public function histories(Request $request, OrderHistoryRepository $orderHistoryRepo, LineItemRepository $lineItemRepo): Response
    {
        $histories = $orderHistoryRepo->findBy([], ['createdAt' => "DESC" ]);

        return $this->render('admin/order/histories.html.twig', [
            'histories' => $histories,
        ]);
    }

    #[Route('/admin/orders/histories/filter', name: 'admin_order_filter_histories')]
    #[IsGranted('ROLE_ADMIN')]
    public function historiesFilter(Request $request, OrderHistoryRepository $orderHistoryRepo, LineItemRepository $lineItemRepo): Response
    {
        $histories = $orderHistoryRepo->filter("Le montant payé");

        return $this->render('admin/order/histories.html.twig', [
            'histories' => $histories,
        ]);
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