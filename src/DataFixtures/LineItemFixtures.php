<?php

namespace App\DataFixtures;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Variant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LineItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Liste des produits disponibles
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
            'tablette_graphique'
        ];

        // Créer quelques lignes de commande de test
        for ($i = 0; $i < 20; $i++) {
            $lineItem = new LineItem();
            
            // Sélectionner un produit aléatoire
            $productRef = $faker->randomElement($products);
            $product = $this->getReference('product_' . $productRef, Product::class);
            
            // Récupérer un variant du produit
            $variantRef = 'variant_' . $productRef . '_' . strtolower($faker->randomElement(['noir', 'standard', 'azerty', '1m']));
            try {
                $variant = $this->getReference($variantRef, Variant::class);
                
                $lineItem->setTitle($variant->getTitle());
                $lineItem->setQuantity($faker->numberBetween(1, 10));
                $lineItem->setPrice($variant->getPrice());
                $lineItem->setProduct($product);
                $lineItem->setVariant($variant);
                
                // Assigner une commande aléatoire
                $order = $this->getReference('order_' . $faker->numberBetween(1, 3), Order::class);
                $lineItem->setOrder($order);

                $manager->persist($lineItem);
            } catch (\Exception $e) {
                // Skip if variant reference doesn't exist
                continue;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            ProductFixtures::class,
            VariantFixtures::class,
        ];
    }
} 