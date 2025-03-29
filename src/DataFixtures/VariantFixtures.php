<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Variant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VariantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $variants = [
            [
                'product' => 'product_ordinateur_portable_pro',
                'title' => 'Noir',
                'price' => 1299.99,
            ],
            [
                'product' => 'product_ordinateur_portable_pro',
                'title' => 'Argent',
                'price' => 1399.99,
            ],
            [
                'product' => 'product_smartphone_premium',
                'title' => 'Noir',
                'price' => 899.99,
            ],
            [
                'product' => 'product_smartphone_premium',
                'title' => 'Or',
                'price' => 999.99,
            ],
            [
                'product' => 'product_cable_usb_type-c',
                'title' => '1m',
                'price' => 19.99,
            ],
            [
                'product' => 'product_cable_usb_type-c',
                'title' => '2m',
                'price' => 29.99,
            ],
            [
                'product' => 'product_processeur_intel_i7',
                'title' => 'Standard',
                'price' => 399.99,
            ],
            [
                'product' => 'product_processeur_intel_i7',
                'title' => 'Overclocked',
                'price' => 449.99,
            ],
            [
                'product' => 'product_carte_graphique_rtx_4070',
                'title' => 'Standard',
                'price' => 799.99,
            ],
            [
                'product' => 'product_carte_graphique_rtx_4070',
                'title' => 'OC Edition',
                'price' => 849.99,
            ],
            [
                'product' => 'product_ssd_1to_nvme',
                'title' => 'Standard',
                'price' => 129.99,
            ],
            [
                'product' => 'product_ecran_27_4k',
                'title' => 'Noir',
                'price' => 449.99,
            ],
            [
                'product' => 'product_ecran_27_4k',
                'title' => 'Blanc',
                'price' => 449.99,
            ],
            [
                'product' => 'product_souris_gaming_pro',
                'title' => 'Noir',
                'price' => 79.99,
            ],
            [
                'product' => 'product_souris_gaming_pro',
                'title' => 'Blanc',
                'price' => 79.99,
            ],
            [
                'product' => 'product_clavier_mecanique_rgb',
                'title' => 'AZERTY',
                'price' => 149.99,
            ],
            [
                'product' => 'product_clavier_mecanique_rgb',
                'title' => 'QWERTY',
                'price' => 149.99,
            ],
            [
                'product' => 'product_casque_audio_sans_fil',
                'title' => 'Noir',
                'price' => 199.99,
            ],
            [
                'product' => 'product_casque_audio_sans_fil',
                'title' => 'Blanc',
                'price' => 199.99,
            ],
            [
                'product' => 'product_webcam_hd',
                'title' => 'Standard',
                'price' => 69.99,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Small',
                'price' => 249.99,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Medium',
                'price' => 299.99,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Large',
                'price' => 349.99,
            ],
        ];

        foreach ($variants as $variantData) {
            $variant = new Variant();
            $variant->setProduct($this->getReference($variantData['product'], Product::class));
            $variant->setTitle($variantData['title']);
            $variant->setPrice($variantData['price']);

            $manager->persist($variant);
            $this->addReference('variant_'.strtolower(str_replace(' ', '_', $variantData['product'])).'_'.strtolower($variantData['title']), $variant);
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
