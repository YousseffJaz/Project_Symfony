<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\LineItem;
use App\Entity\Product;
use App\Entity\Admin;
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
                'status' => 1,
                'total' => 1339.97,
                'note' => 'Commande urgente',
            ],
            [
                'reference' => 'order_2',
                'admin' => 'admin_user1',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'phone' => '0623456789',
                'products' => [
                    [
                        'product' => 'product_smartphone_premium',
                        'title' => 'Smartphone Premium',
                        'quantity' => 1,
                        'price' => 899.99,
                    ],
                ],
                'status' => 0,
                'total' => 899.99,
                'note' => 'Livraison standard',
            ],
            [
                'reference' => 'order_3',
                'admin' => 'admin_user2',
                'firstname' => 'Robert',
                'lastname' => 'Johnson',
                'phone' => '0634567890',
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
                'status' => 2,
                'total' => 419.98,
                'note' => 'Appeler avant livraison',
            ],
        ];

        foreach ($orders as $orderData) {
            $order = new Order();
            $order->setAdmin($this->getReference($orderData['admin'], Admin::class));
            $order->setFirstname($orderData['firstname']);
            $order->setLastname($orderData['lastname']);
            $order->setPhone($orderData['phone']);
            $order->setStatus($orderData['status']);
            $order->setTotal($orderData['total']);
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