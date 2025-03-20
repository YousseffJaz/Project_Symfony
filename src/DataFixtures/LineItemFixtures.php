<?php

namespace App\DataFixtures;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LineItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques lignes de commande de test
        for ($i = 0; $i < 20; $i++) {
            $lineItem = new LineItem();
            $lineItem->setTitle($faker->words(3, true));
            $lineItem->setQuantity($faker->numberBetween(1, 10));
            $lineItem->setPrice($faker->randomFloat(2, 10, 1000));
            $lineItem->setPriceList('default');
            
            // Assigner une commande aléatoire
            $order = $this->getReference('order_' . $faker->numberBetween(1, 3), Order::class);
            $lineItem->setOrderItem($order);
            
            // Assigner un produit aléatoire
            $product = $this->getReference('product_' . $faker->randomElement([
                'ordinateur_portable_pro',
                'smartphone_premium',
                'cable_usb_type-c',
                'processeur_intel_i7'
            ]), Product::class);
            $lineItem->setProduct($product);

            $manager->persist($lineItem);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            ProductFixtures::class,
        ];
    }
} 