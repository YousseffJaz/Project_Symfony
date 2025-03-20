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
use Faker\Factory;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    private const PAYMENT_TYPES = [
        0 => 'Non défini',
        1 => 'Carte bancaire',
        2 => 'Virement',
        3 => 'Espèces',
        4 => 'Chèque'
    ];

    private const ORDER_STATUSES = [
        0 => 'En attente',
        1 => 'En cours',
        2 => 'Livré',
        3 => 'Annulé'
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Liste des produits disponibles avec leurs prix
        $products = [
            'product_ordinateur_portable_pro' => ['title' => 'Ordinateur Portable Pro', 'price' => 1299.99],
            'product_smartphone_premium' => ['title' => 'Smartphone Premium', 'price' => 899.99],
            'product_cable_usb_type-c' => ['title' => 'Cable USB Type-C', 'price' => 19.99],
            'product_processeur_intel_i7' => ['title' => 'Processeur Intel i7', 'price' => 399.99],
        ];

        // Liste des admins disponibles
        $admins = ['admin_test', 'admin_user1', 'admin_user2'];

        // Générer 100 commandes
        for ($i = 1; $i <= 100; $i++) {
            $order = new Order();
            
            // Informations de base
            $adminRef = $faker->randomElement($admins);
            $order->setAdmin($this->getReference($adminRef, Admin::class));
            $order->setFirstname($faker->firstName());
            $order->setLastname($faker->lastName());
            $order->setPhone($faker->phoneNumber());
            $order->setEmail($faker->email());
            $order->setAddress($faker->streetAddress() . ', ' . $faker->postcode() . ' ' . $faker->city());
            $order->setIdentifier(sprintf('CMD-%s-%03d', $faker->dateTimeBetween('-1 year')->format('Y'), $i));

            // Date de création (répartie sur les 12 derniers mois)
            $createdAt = $faker->dateTimeBetween('-1 year');
            $order->setCreatedAt($createdAt);

            // Statut de la commande
            $status = $faker->randomElement(array_keys(self::ORDER_STATUSES));
            $order->setStatus($status);
            $order->setOrderStatus($faker->numberBetween(0, 2));

            // Produits de la commande
            $numberOfProducts = $faker->numberBetween(1, 4);
            $total = 0;
            
            // Sélection aléatoire des produits
            $productRefs = array_keys($products);
            shuffle($productRefs);
            $selectedProductRefs = array_slice($productRefs, 0, $numberOfProducts);

            foreach ($selectedProductRefs as $productRef) {
                $productInfo = $products[$productRef];
                $quantity = $faker->numberBetween(1, 3);
                $lineItem = new LineItem();
                $lineItem->setProduct($this->getReference($productRef, Product::class));
                $lineItem->setTitle($productInfo['title']);
                $lineItem->setQuantity($quantity);
                $lineItem->setPrice($productInfo['price']);
                $lineItem->setOrderItem($order);
                
                $total += $productInfo['price'] * $quantity;
                $manager->persist($lineItem);
            }

            // Frais de livraison et remises
            $shippingCost = $total > 1000 ? 0 : $faker->randomElement([0, 15, 20, 25]);
            $discount = $total > 500 ? $faker->randomElement([0, 50, 100, 150]) : 0;
            
            $finalTotal = $total + $shippingCost - $discount;
            
            $order->setTotal($finalTotal);
            $order->setShippingCost($shippingCost);
            $order->setDiscount($discount);

            // Paiement
            if ($status !== 3) { // Si la commande n'est pas annulée
                $paymentType = $faker->randomElement([1, 2, 3, 4]); // Exclure le type 0 (Non défini)
                $paid = $status === 2 ? $finalTotal : ($faker->boolean(70) ? $finalTotal : 0);
            } else {
                $paymentType = 0;
                $paid = 0;
            }
            
            $order->setPaid($paid);
            $order->setPaymentType($paymentType);
            $order->setPaymentMethod($paymentType);

            // Note
            $notes = [
                'Livraison standard',
                'Commande urgente',
                'Appeler avant livraison',
                'Livraison à l\'étage',
                'Client professionnel',
                'Commande annulée - Rupture de stock',
                'Instructions spéciales de livraison',
            ];
            $order->setNote($faker->randomElement($notes));

            // Historique de la commande
            $this->createOrderHistory($manager, $order, $status, $createdAt);

            $manager->persist($order);
            $this->addReference('order_' . $i, $order);
        }

        $manager->flush();
    }

    private function createOrderHistory(ObjectManager $manager, Order $order, int $status, \DateTime $createdAt): void
    {
        // Création de la commande
        $history = new OrderHistory();
        $history->setTitle('Commande créée');
        $history->setCreatedAt(clone $createdAt);
        $history->setInvoice($order);
        $history->setAdmin($order->getAdmin());
        $manager->persist($history);

        if ($status === 3) { // Commande annulée
            $cancelDate = clone $createdAt;
            $cancelDate->modify('+' . rand(1, 24) . ' hours');
            
            $history = new OrderHistory();
            $history->setTitle('Commande annulée');
            $history->setCreatedAt($cancelDate);
            $history->setInvoice($order);
            $history->setAdmin($order->getAdmin());
            $manager->persist($history);
        } else {
            // Paiement
            if ($order->getPaid() > 0) {
                $paymentDate = clone $createdAt;
                $paymentDate->modify('+' . rand(5, 60) . ' minutes');
                
                $history = new OrderHistory();
                $history->setTitle('Paiement validé');
                $history->setCreatedAt($paymentDate);
                $history->setInvoice($order);
                $history->setAdmin($order->getAdmin());
                $manager->persist($history);
            }

            // Si la commande est en cours ou livrée
            if ($status >= 1) {
                $prepDate = clone $createdAt;
                $prepDate->modify('+' . rand(1, 24) . ' hours');
                
                $history = new OrderHistory();
                $history->setTitle('En préparation');
                $history->setCreatedAt($prepDate);
                $history->setInvoice($order);
                $history->setAdmin($order->getAdmin());
                $manager->persist($history);
            }

            // Si la commande est livrée
            if ($status === 2) {
                $deliveryDate = clone $createdAt;
                $deliveryDate->modify('+' . rand(2, 5) . ' days');
                
                $history = new OrderHistory();
                $history->setTitle('Commande livrée');
                $history->setCreatedAt($deliveryDate);
                $history->setInvoice($order);
                $history->setAdmin($order->getAdmin());
                $manager->persist($history);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
            ProductFixtures::class,
        ];
    }
} 