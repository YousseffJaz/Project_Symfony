<?php

namespace App\DataFixtures;

use App\Entity\StockList;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StockListFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques listes de stock de test
        for ($i = 0; $i < 5; $i++) {
            $stockList = new StockList();
            $stockList->setName($faker->word());
            $stockList->setQuantity($faker->numberBetween(0, 100));
            
            // Assigner un produit aléatoire
            $product = $this->getReference('product_' . $faker->randomElement([
                'ordinateur_portable_pro',
                'smartphone_premium',
                'cable_usb_type-c',
                'processeur_intel_i7'
            ]), Product::class);
            $stockList->setProduct($product);

            $manager->persist($stockList);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
} 