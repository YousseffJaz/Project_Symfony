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