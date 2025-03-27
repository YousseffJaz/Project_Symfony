<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\LineItem;
use App\Entity\Product;
use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\OrderHistory;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\PaymentMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    private function getOrderStatuses(): array
    {
        return [
            OrderStatus::WAITING->value => 'En attente',
            OrderStatus::PARTIAL->value => 'En cours',
            OrderStatus::PAID->value => 'Livré',
            OrderStatus::REFUND->value => 'Annulé'
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Liste des produits disponibles avec leurs prix
        $products = [
            'product_ordinateur_portable_pro' => ['title' => 'Ordinateur Portable Pro', 'price' => 1299.99],
            'product_smartphone_premium' => ['title' => 'Smartphone Premium', 'price' => 899.99],
            'product_cable_usb_type-c' => ['title' => 'Cable USB Type-C', 'price' => 19.99],
            'product_processeur_intel_i7' => ['title' => 'Processeur Intel i7', 'price' => 399.99],
            'product_carte_graphique_rtx_4070' => ['title' => 'Carte Graphique RTX 4070', 'price' => 799.99],
            'product_ssd_1to_nvme' => ['title' => 'SSD 1To NVMe', 'price' => 129.99],
            'product_ecran_27_4k' => ['title' => 'Ecran 27 4K', 'price' => 449.99],
            'product_souris_gaming_pro' => ['title' => 'Souris Gaming Pro', 'price' => 79.99],
            'product_clavier_mecanique_rgb' => ['title' => 'Clavier Mecanique RGB', 'price' => 149.99],
            'product_casque_audio_sans_fil' => ['title' => 'Casque Audio Sans Fil', 'price' => 199.99],
            'product_webcam_hd' => ['title' => 'Webcam HD', 'price' => 69.99],
            'product_tablette_graphique' => ['title' => 'Tablette Graphique', 'price' => 299.99]
        ];

        // Liste des admins disponibles
        $admins = ['admin_admin', 'admin_manager', 'admin_staff'];

        // Générer 100 commandes
        for ($i = 1; $i <= 100; $i++) {
            $order = new Order();
            
            // Associer un client existant
            $customerRefs = [
                'customer_jean_dupont',
                'customer_marie_martin',
                'customer_pierre_bernard',
                'customer_sophie_dubois',
                'customer_lucas_petit',
                'customer_emma_leroy',
                'customer_thomas_moreau',
                'customer_lea_roux',
                'customer_hugo_simon',
                'customer_chloe_michel',
                'customer_antoine_laurent',
                'customer_julie_garcia',
                'customer_nicolas_david',
                'customer_sarah_bertrand',
                'customer_maxime_robert'
            ];
            $customerRef = $this->faker->randomElement($customerRefs);
            $customer = $this->getReference($customerRef, Customer::class);
            $order->setCustomer($customer);
            
            // Informations de base
            $adminRef = $this->faker->randomElement($admins);
            $order->setAdmin($this->getReference($adminRef, Admin::class));

            // Date de création (répartie sur les 12 derniers mois)
            $createdAt = $this->faker->dateTimeBetween('-1 year');
            $order->setCreatedAt($createdAt);

            // Statut de la commande
            $status = $this->faker->randomElement(array_keys($this->getOrderStatuses()));
            $order->setStatus($status);
            $order->setOrderStatus($this->faker->numberBetween(0, 2));

            // Produits de la commande
            $numberOfProducts = $this->faker->numberBetween(1, 4);
            $total = 0;
            
            // Sélection aléatoire des produits
            $productRefs = array_keys($products);
            shuffle($productRefs);
            $selectedProductRefs = array_slice($productRefs, 0, $numberOfProducts);

            foreach ($selectedProductRefs as $productRef) {
                $productInfo = $products[$productRef];
                $quantity = $this->faker->numberBetween(1, 3);
                $lineItem = new LineItem();
                $lineItem->setProduct($this->getReference($productRef, Product::class));
                $lineItem->setTitle($productInfo['title']);
                $lineItem->setQuantity($quantity);
                $lineItem->setPrice($productInfo['price']);
                $lineItem->setOrder($order);
                
                $total += $productInfo['price'] * $quantity;
                $manager->persist($lineItem);
            }

            // Frais de livraison et remises
            $shippingCost = $total > 1000 ? 0 : $this->faker->randomElement([0, 15, 20, 25]);
            $discount = $total > 500 ? $this->faker->randomElement([0, 50, 100, 150]) : 0;
            
            $finalTotal = $total + $shippingCost - $discount;
            
            $order->setTotal($finalTotal);
            $order->setShippingCost($shippingCost);
            $order->setDiscount($discount);

            // Paiement
            if ($status !== OrderStatus::REFUND->value) { // Si la commande n'est pas annulée
                $paymentType = $this->faker->randomElement([
                    PaymentType::ONLINE->value,
                    PaymentType::LOCAL->value
                ]);
                $paymentMethod = $this->faker->randomElement([
                    PaymentMethod::CASH->value,
                    PaymentMethod::TRANSCASH->value,
                    PaymentMethod::CARD->value,
                    PaymentMethod::PAYPAL->value,
                    PaymentMethod::PCS->value,
                    PaymentMethod::CHECK->value,
                    PaymentMethod::PAYSAFECARD->value,
                    PaymentMethod::BANK->value
                ]);
                $paid = $status === OrderStatus::PAID->value ? $finalTotal : ($this->faker->boolean(70) ? $finalTotal : 0);
            } else {
                $paymentType = PaymentType::ONLINE->value;
                $paymentMethod = PaymentMethod::CARD->value;
                $paid = 0;
            }
            
            $order->setPaid($paid);
            $order->setPaymentType($paymentType);
            $order->setPaymentMethod($paymentMethod);

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
        }
        
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
        if ($status >= 1 && $status <= 2) {
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

    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
            ProductFixtures::class,
            CustomerFixtures::class,
        ];
    }
} 