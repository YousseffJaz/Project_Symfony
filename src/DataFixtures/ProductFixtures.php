<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'title' => 'Ordinateur Portable Pro',
                'price' => 1299.99,
                'category' => 'category_informatique',
                'purchasePrice' => 999.99,
                'alert' => 5,
            ],
            [
                'title' => 'Smartphone Premium',
                'price' => 899.99,
                'category' => 'category_electronique',
                'purchasePrice' => 699.99,
                'alert' => 8,
            ],
            [
                'title' => 'Cable USB Type-C',
                'price' => 19.99,
                'category' => 'category_accessoires',
                'purchasePrice' => 5.99,
                'alert' => 20,
            ],
            [
                'title' => 'Processeur Intel i7',
                'price' => 399.99,
                'category' => 'category_composants',
                'purchasePrice' => 299.99,
                'alert' => 3,
            ],
            [
                'title' => 'Carte Graphique RTX 4070',
                'price' => 799.99,
                'category' => 'category_composants',
                'purchasePrice' => 599.99,
                'alert' => 2,
            ],
            [
                'title' => 'SSD 1To NVMe',
                'price' => 129.99,
                'category' => 'category_composants',
                'purchasePrice' => 89.99,
                'alert' => 10,
            ],
            [
                'title' => 'Ecran 27 4K',
                'price' => 449.99,
                'category' => 'category_informatique',
                'purchasePrice' => 349.99,
                'alert' => 4,
            ],
            [
                'title' => 'Souris Gaming Pro',
                'price' => 79.99,
                'category' => 'category_accessoires',
                'purchasePrice' => 39.99,
                'alert' => 15,
            ],
            [
                'title' => 'Clavier Mecanique RGB',
                'price' => 149.99,
                'category' => 'category_accessoires',
                'purchasePrice' => 89.99,
                'alert' => 12,
            ],
            [
                'title' => 'Casque Audio Sans Fil',
                'price' => 199.99,
                'category' => 'category_electronique',
                'purchasePrice' => 129.99,
                'alert' => 6,
            ],
            [
                'title' => 'Webcam HD',
                'price' => 69.99,
                'category' => 'category_accessoires',
                'purchasePrice' => 39.99,
                'alert' => 10,
            ],
            [
                'title' => 'Tablette Graphique',
                'price' => 299.99,
                'category' => 'category_informatique',
                'purchasePrice' => 199.99,
                'alert' => 5,
            ]
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setTitle($productData['title']);
            $product->setPrice($productData['price']);
            $product->setCategory($this->getReference($productData['category'], Category::class));
            $product->setPurchasePrice($productData['purchasePrice']);
            $product->setAlert($productData['alert']);
            
            $manager->persist($product);
            $reference = 'product_' . strtolower(str_replace(' ', '_', $productData['title']));
            $this->addReference($reference, $product);
            echo "Created reference: " . $reference . "\n";
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
} 