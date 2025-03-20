<?php

namespace App\DataFixtures;

use App\Entity\Preorder;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PreorderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $preorders = [
            [
                'product' => 'product_ordinateur_portable_pro',
                'title' => 'Précommande Ordinateur Portable Pro',
                'quantity' => 5,
            ],
            [
                'product' => 'product_smartphone_premium',
                'title' => 'Précommande Smartphone Premium',
                'quantity' => 10,
            ],
            [
                'product' => 'product_processeur_intel_i7',
                'title' => 'Précommande Processeur Intel i7',
                'quantity' => 3,
            ],
        ];

        foreach ($preorders as $preorderData) {
            $preorder = new Preorder();
            $preorder->setProduct($this->getReference($preorderData['product'], Product::class));
            $preorder->setTitle($preorderData['title']);
            $preorder->setQuantity($preorderData['quantity']);
            
            $manager->persist($preorder);
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