<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\OrderHistory;
use App\Enum\OrderStatus;
use App\Event\PreOrderValidationEvent;
use App\Form\AdminOrderType;
use App\Repository\AdminRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\VariantRepository;
use App\Service\Order\OrderExportService;
use App\Service\Order\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderService $orderService,
        private OrderExportService $orderExportService,
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    private function getAdmin(): Admin
    {
        /* @var Admin */
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
            $start = new \DateTime($currentYear.'-01-01');
            $start->setTime(0, 0, 0);
            $end = new \DateTime($currentYear.'-12-31');
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

    #[Route('/admin/orders/customer/autocomplete', name: 'admin_order_customer')]
    #[IsGranted('ROLE_ADMIN')]
    public function customer(Request $request): Response
    {
        $search = $request->query->get('term');
        $customers = $this->entityManager->getRepository(Customer::class)->createQueryBuilder('c')
            ->where('c.firstname LIKE :search OR c.lastname LIKE :search OR c.email LIKE :search')
            ->setParameter('search', '%'.$search.'%')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($customers as $customer) {
            $data[] = [
                'id' => $customer->getId(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'email' => $customer->getEmail(),
                'address' => $customer->getAddress(),
                'phone' => $customer->getPhone(),
                'label' => $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')',
                'value' => $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')',
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/admin/orders/new', name: 'admin_order_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(ProductRepository $productRepository, VariantRepository $variantRepo, Request $request, EntityManagerInterface $manager, StockListRepository $stockRepo, AdminRepository $adminRepo): Response
    {
        $order = new Order();
        $order->setCreatedAt(new \DateTime());
        $variants = $variantRepo->findBy(['archive' => false], ['title' => 'ASC']);
        $products = $productRepository->findBy(['archive' => false], ['title' => 'ASC']);
        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stockList = $request->request->all('stockListId');
            $variantId = $request->request->all('variantId');
            $title = $request->request->all('title');
            $quantity = $request->request->all('quantity');
            $price = $request->request->all('price');

            if ($variantId) {
                for ($i = 0; $i < count($variantId); ++$i) {
                    $variant = $variantRepo->findOneById((int)$variantId[$i]);

                    if ($variant) {
                        $product = $variant->getProduct();
                        $stock = $stockRepo->findOneBy(['name' => $stockList[$i], 'product' => $product]);

                        if ($stock) {
                            $item = new LineItem();
                            $item->setTitle($title[$i]);
                            $item->setQuantity((int)$quantity[$i]);
                            $item->setPrice((float)$price[$i]);
                            $item->setVariant($variant);
                            $item->setProduct($product);
                            $item->setOrder($order);
                            $item->setStock($stock);

                            $manager->persist($item);
                            $order->addLineItem($item);
                        }
                    }
                }
            }

            if (!$order->getLineItems()->toArray()) {
                $this->addFlash(
                    'error',
                    'Il faut ajouter un produit pour créer une commande !'
                );

                return $this->redirectToRoute('admin_order_new');
            }

            // Validation du stock via l'événement
            try {
                $event = new PreOrderValidationEvent($order);
                $this->eventDispatcher->dispatch($event, PreOrderValidationEvent::NAME);
            } catch (BadRequestHttpException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('admin_order_new');
            }

            // Mise à jour des stocks après validation
            foreach ($order->getLineItems() as $item) {
                $stock = $item->getStock();
                $stock->setQuantity($stock->getQuantity() - $item->getQuantity());
            }

            if ($order->getTotal() < $order->getPaid()) {
                $order->setStatus(OrderStatus::REFUND->value);
            } elseif ($order->getTotal() == $order->getPaid()) {
                $order->setStatus(OrderStatus::PAID->value);
            } elseif (0 != $order->getPaid()) {
                $order->setStatus(OrderStatus::PARTIAL->value);
            } else {
                $order->setStatus(OrderStatus::WAITING->value);
            }

            /** @var Admin */
            $admin = $this->getAdmin();
            $order->setAdmin($admin);

            // Ajout de l'historique pour chaque ligne
            foreach ($order->getLineItems() as $item) {
                $history = new OrderHistory();
                $history->setTitle("Le produit '{$item->getVariant()->getTitle()}' a été ajouté en '{$item->getQuantity()}' exemplaire(s) pour '{$item->getPrice()}€'");
                $history->setInvoice($order);
                $history->setAdmin($admin);
                $manager->persist($history);
            }

            $manager->persist($order);
            $manager->flush();

            $this->addFlash(
                'success',
                'Une nouvelle commande à été ajoutée !'
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
            try {
                // Vérifier que chaque ligne a un variant et un stock associé
                foreach ($order->getLineItems() as $item) {
                    $variant = $item->getVariant();
                    if (!$variant) {
                        throw new BadRequestHttpException('Un produit de la commande n\'a pas de variant associé.');
                    }

                    if (!$item->getStock()) {
                        throw new BadRequestHttpException(sprintf('Aucun stock n\'est associé au produit "%s". Veuillez sélectionner un stock.', $variant->getTitle()));
                    }
                }

                // Validation du stock via l'événement
                $event = new PreOrderValidationEvent($order, false, $this->entityManager);
                $this->eventDispatcher->dispatch($event, PreOrderValidationEvent::NAME);

                // Mise à jour des stocks uniquement pour les nouveaux produits ou les quantités modifiées
                foreach ($order->getLineItems() as $item) {
                    $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($item);
                    if (!$originalData || $originalData['quantity'] !== $item->getQuantity()) {
                        $stock = $item->getStock();
                        $variant = $item->getVariant();
                        $quantityDiff = $originalData ? ($item->getQuantity() - $originalData['quantity']) : $item->getQuantity();

                        // Si la quantité a diminué, on remet en stock la différence
                        if ($quantityDiff < 0) {
                            $stock->setQuantity($stock->getQuantity() + abs($quantityDiff));

                            // Ajouter une entrée dans l'historique pour le retour en stock
                            $history = new OrderHistory();
                            $history->setTitle(sprintf(
                                "Retour en stock de %d unité(s) du produit '%s' dans le stock '%s'",
                                abs($quantityDiff),
                                $variant->getTitle(),
                                $stock->getName()
                            ));
                            $history->setInvoice($order);
                            $history->setAdmin($this->getAdmin());
                            $this->entityManager->persist($history);
                        } else {
                            // Si la quantité a augmenté ou si c'est un nouveau produit
                            $stock->setQuantity($stock->getQuantity() - $quantityDiff);
                        }
                    }
                }

                // Mise à jour du statut de la commande
                if ($order->getTotal() < $order->getPaid()) {
                    $order->setStatus(OrderStatus::REFUND->value);
                } elseif ($order->getTotal() == $order->getPaid()) {
                    $order->setStatus(OrderStatus::PAID->value);
                } elseif (0 != $order->getPaid()) {
                    $order->setStatus(OrderStatus::PARTIAL->value);
                } else {
                    $order->setStatus(OrderStatus::WAITING->value);
                }

                // Ajout dans l'historique uniquement pour les modifications de quantité
                foreach ($order->getLineItems() as $item) {
                    $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($item);
                    if (!$originalData || $originalData['quantity'] !== $item->getQuantity()) {
                        $variant = $item->getVariant();
                        $history = new OrderHistory();
                        if (!$originalData) {
                            $action = 'ajouté';
                        } else {
                            $quantityDiff = $item->getQuantity() - $originalData['quantity'];
                            $action = $quantityDiff > 0 ? 'augmenté' : 'diminué';
                        }
                        $history->setTitle("Le produit '{$variant->getTitle()}' a été {$action} à '{$item->getQuantity()}' exemplaire(s) pour '{$item->getPrice()}€'");
                        $history->setInvoice($order);
                        $history->setAdmin($this->getAdmin());
                        $this->entityManager->persist($history);
                    }
                }

                $this->entityManager->flush();

                $this->addFlash(
                    'success',
                    'La commande a été modifiée !'
                );

                return $this->redirectToRoute('admin_order_index');
            } catch (BadRequestHttpException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $products = $productRepository->findBy(['archive' => false], ['title' => 'ASC']);

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'products' => $products,
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
        } elseif ($start && $end) {
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
            $yearStart = new \DateTime($currentYear.'-01-01');
            $yearStart->setTime(0, 0, 0);
            $yearEnd = new \DateTime($currentYear.'-12-31');
            $yearEnd->setTime(23, 59, 59);

            $orders = $this->orderRepository->findByStartAndEnd(
                $yearStart->format('Y-m-d'),
                $yearEnd->format('Y-m-d')
            );
        }

        return $this->render('admin/order/print.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/admin/lineitem/delete/{id}', name: 'admin_lineitem_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteLineitem(LineItem $lineItem): Response
    {
        $this->orderService->deleteLineItem($lineItem, $this->getAdmin());

        return new JsonResponse([
            'message' => 'success',
        ]);
    }

    #[Route('/admin/orders/delete/{id}', name: 'admin_order_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOrder(Order $order): Response
    {
        $this->orderService->deleteOrder($order);

        $this->addFlash(
            'success',
            'La commande a été supprimée !'
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
        $french_months = ['Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Aoùt', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
        $english_months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return str_replace($english_months, $french_months, $date);
    }
}
