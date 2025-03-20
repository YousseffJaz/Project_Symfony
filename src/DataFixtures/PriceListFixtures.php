<?php

namespace App\DataFixtures;

use App\Entity\PriceList;
use App\Entity\Variant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PriceListFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $priceLists = [
            [
                'variant' => 'variant_product_ordinateur_portable_pro_noir',
                'title' => 'Prix Standard',
                'price' => 1299.99,
            ],
            [
                'variant' => 'variant_product_ordinateur_portable_pro_argent',
                'title' => 'Prix Standard',
                'price' => 1399.99,
            ],
            [
                'variant' => 'variant_product_smartphone_premium_noir',
                'title' => 'Prix Standard',
                'price' => 899.99,
            ],
            [
                'variant' => 'variant_product_smartphone_premium_or',
                'title' => 'Prix Standard',
                'price' => 999.99,
            ],
            [
                'variant' => 'variant_product_cable_usb_type-c_1m',
                'title' => 'Prix Standard',
                'price' => 19.99,
            ],
            [
                'variant' => 'variant_product_cable_usb_type-c_2m',
                'title' => 'Prix Standard',
                'price' => 29.99,
            ],
            [
                'variant' => 'variant_product_processeur_intel_i7_standard',
                'title' => 'Prix Standard',
                'price' => 399.99,
            ],
            [
                'variant' => 'variant_product_processeur_intel_i7_overclocked',
                'title' => 'Prix Standard',
                'price' => 449.99,
            ],
        ];

        foreach ($priceLists as $priceListData) {
            $priceList = new PriceList();
            $priceList->setVariant($this->getReference($priceListData['variant'], Variant::class));
            $priceList->setTitle($priceListData['title']);
            $priceList->setPrice($priceListData['price']);
            
            $manager->persist($priceList);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VariantFixtures::class,
        ];
    }
} 