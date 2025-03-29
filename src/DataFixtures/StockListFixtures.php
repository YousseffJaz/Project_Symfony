<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\StockList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StockListFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $cities = ['Paris', 'Lyon'];
        $products = [
            'ordinateur_portable_pro',
            'smartphone_premium',
            'cable_usb_type-c',
            'processeur_intel_i7',
            'carte_graphique_rtx_4070',
            'ssd_1to_nvme',
            'ecran_27_4k',
            'souris_gaming_pro',
            'clavier_mecanique_rgb',
            'casque_audio_sans_fil',
            'webcam_hd',
            'tablette_graphique',
        ];

        foreach ($cities as $city) {
            foreach ($products as $productRef) {
                $stockList = new StockList();
                $stockList->setName($city);
                $stockList->setQuantity($faker->numberBetween(10, 100));

                $product = $this->getReference('product_'.$productRef, Product::class);
                $stockList->setProduct($product);

                $manager->persist($stockList);
            }
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
