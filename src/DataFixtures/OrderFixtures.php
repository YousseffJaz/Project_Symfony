<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\LineItem;
use App\Entity\Product;
use App\Entity\Admin;
use App\Entity\OrderHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $orders = [
            [
                'reference' => 'order_1',
                'admin' => 'admin_test',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'phone' => '0612345678',
                'address' => '123 Rue de la Paix, 75001 Paris',
                'identifier' => 'CMD-2024-001',
                'createdAt' => new \DateTime('2024-03-15 10:30:00'),
                'products' => [
                    [
                        'product' => 'product_ordinateur_portable_pro',
                        'title' => 'Ordinateur Portable Pro',
                        'quantity' => 1,
                        'price' => 1299.99,
                    ],
                    [
                        'product' => 'product_cable_usb_type-c',
                        'title' => 'Cable USB Type-C',
                        'quantity' => 2,
                        'price' => 19.99,
                    ],
                ],
                'status' => 2, // Livré
                'orderStatus' => 1,
                'total' => 1339.97,
                'shippingCost' => 15.00,
                'discount' => 0,
                'paid' => 1339.97,
                'paymentType' => 1, // CB
                'paymentMethod' => 1,
                'note' => 'Commande urgente',
                'history' => [
                    ['title' => 'Commande créée', 'date' => '2024-03-15 10:30:00'],
                    ['title' => 'Paiement validé', 'date' => '2024-03-15 10:35:00'],
                    ['title' => 'Commande expédiée', 'date' => '2024-03-16 14:20:00'],
                    ['title' => 'Commande livrée', 'date' => '2024-03-17 09:45:00'],
                ]
            ],
            [
                'reference' => 'order_2',
                'admin' => 'admin_user1',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'phone' => '0623456789',
                'address' => '456 Avenue des Champs-Élysées, 75008 Paris',
                'identifier' => 'CMD-2024-002',
                'createdAt' => new \DateTime('2024-02-20 15:45:00'),
                'products' => [
                    [
                        'product' => 'product_smartphone_premium',
                        'title' => 'Smartphone Premium',
                        'quantity' => 1,
                        'price' => 899.99,
                    ],
                    [
                        'product' => 'product_cable_usb_type-c',
                        'title' => 'Cable USB Type-C',
                        'quantity' => 1,
                        'price' => 19.99,
                    ],
                    [
                        'product' => 'product_processeur_intel_i7',
                        'title' => 'Processeur Intel i7',
                        'quantity' => 1,
                        'price' => 399.99,
                    ],
                ],
                'status' => 1, // En cours
                'orderStatus' => 2,
                'total' => 1319.97,
                'shippingCost' => 20.00,
                'discount' => 50.00,
                'paid' => 1289.97,
                'paymentType' => 2, // Virement
                'paymentMethod' => 2,
                'note' => 'Livraison standard',
                'history' => [
                    ['title' => 'Commande créée', 'date' => '2024-02-20 15:45:00'],
                    ['title' => 'Paiement en attente', 'date' => '2024-02-20 15:50:00'],
                ]
            ],
            [
                'reference' => 'order_3',
                'admin' => 'admin_user2',
                'firstname' => 'Robert',
                'lastname' => 'Johnson',
                'phone' => '0634567890',
                'address' => '789 Boulevard Saint-Germain, 75006 Paris',
                'identifier' => 'CMD-2023-150',
                'createdAt' => new \DateTime('2023-12-10 09:15:00'),
                'products' => [
                    [
                        'product' => 'product_processeur_intel_i7',
                        'title' => 'Processeur Intel i7',
                        'quantity' => 1,
                        'price' => 399.99,
                    ],
                    [
                        'product' => 'product_cable_usb_type-c',
                        'title' => 'Cable USB Type-C',
                        'quantity' => 1,
                        'price' => 19.99,
                    ],
                ],
                'status' => 3, // Annulé
                'orderStatus' => 0,
                'total' => 419.98,
                'shippingCost' => 10.00,
                'discount' => 0,
                'paid' => 0,
                'paymentType' => 0,
                'paymentMethod' => 0,
                'note' => 'Commande annulée - Rupture de stock',
                'history' => [
                    ['title' => 'Commande créée', 'date' => '2023-12-10 09:15:00'],
                    ['title' => 'Commande annulée', 'date' => '2023-12-10 14:30:00'],
                ]
            ],
            [
                'reference' => 'order_4',
                'admin' => 'admin_test',
                'firstname' => 'Marie',
                'lastname' => 'Dubois',
                'phone' => '0645678901',
                'address' => '321 Rue de Rivoli, 75004 Paris',
                'identifier' => 'CMD-2024-003',
                'createdAt' => new \DateTime('2024-03-01 11:20:00'),
                'products' => [
                    [
                        'product' => 'product_smartphone_premium',
                        'title' => 'Smartphone Premium',
                        'quantity' => 2,
                        'price' => 899.99,
                    ],
                ],
                'status' => 1, // En cours
                'orderStatus' => 1,
                'total' => 1799.98,
                'shippingCost' => 0, // Livraison gratuite
                'discount' => 100.00,
                'paid' => 1699.98,
                'paymentType' => 1, // CB
                'paymentMethod' => 1,
                'note' => 'Commande professionnelle',
                'history' => [
                    ['title' => 'Commande créée', 'date' => '2024-03-01 11:20:00'],
                    ['title' => 'Paiement validé', 'date' => '2024-03-01 11:25:00'],
                    ['title' => 'En préparation', 'date' => '2024-03-02 09:00:00'],
                ]
            ],
        ];

        foreach ($orders as $orderData) {
            $order = new Order();
            $order->setAdmin($this->getReference($orderData['admin'], Admin::class));
            $order->setFirstname($orderData['firstname']);
            $order->setLastname($orderData['lastname']);
            $order->setPhone($orderData['phone']);
            $order->setAddress($orderData['address']);
            $order->setIdentifier($orderData['identifier']);
            $order->setCreatedAt($orderData['createdAt']);
            $order->setStatus($orderData['status']);
            $order->setOrderStatus($orderData['orderStatus']);
            $order->setTotal($orderData['total']);
            $order->setShippingCost($orderData['shippingCost']);
            $order->setDiscount($orderData['discount']);
            $order->setPaid($orderData['paid']);
            $order->setPaymentType($orderData['paymentType']);
            $order->setPaymentMethod($orderData['paymentMethod']);
            $order->setNote($orderData['note']);
            
            foreach ($orderData['products'] as $productData) {
                $lineItem = new LineItem();
                $lineItem->setProduct($this->getReference($productData['product'], Product::class));
                $lineItem->setTitle($productData['title']);
                $lineItem->setQuantity($productData['quantity']);
                $lineItem->setPrice($productData['price']);
                $lineItem->setOrderItem($order);
                
                $manager->persist($lineItem);
            }

            // Création de l'historique des commandes
            foreach ($orderData['history'] as $historyData) {
                $history = new OrderHistory();
                $history->setTitle($historyData['title']);
                $history->setCreatedAt(new \DateTime($historyData['date']));
                $history->setInvoice($order);
                $history->setAdmin($order->getAdmin());
                
                $manager->persist($history);
            }
            
            $manager->persist($order);
            $this->addReference($orderData['reference'], $order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
            ProductFixtures::class,
        ];
    }
} 