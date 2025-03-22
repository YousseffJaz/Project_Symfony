<?php

namespace App\DataFixtures;

use App\Entity\Variant;
use App\Entity\Product;
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
                'archive' => false,
            ],
            [
                'product' => 'product_ordinateur_portable_pro',
                'title' => 'Argent',
                'archive' => false,
            ],
            [
                'product' => 'product_smartphone_premium',
                'title' => 'Noir',
                'archive' => false,
            ],
            [
                'product' => 'product_smartphone_premium',
                'title' => 'Or',
                'archive' => false,
            ],
            [
                'product' => 'product_cable_usb_type-c',
                'title' => '1m',
                'archive' => false,
            ],
            [
                'product' => 'product_cable_usb_type-c',
                'title' => '2m',
                'archive' => false,
            ],
            [
                'product' => 'product_processeur_intel_i7',
                'title' => 'Standard',
                'archive' => false,
            ],
            [
                'product' => 'product_processeur_intel_i7',
                'title' => 'Overclocked',
                'archive' => false,
            ],
            [
                'product' => 'product_carte_graphique_rtx_4070',
                'title' => 'Standard',
                'archive' => false,
            ],
            [
                'product' => 'product_carte_graphique_rtx_4070',
                'title' => 'OC Edition',
                'archive' => false,
            ],
            [
                'product' => 'product_ssd_1to_nvme',
                'title' => 'Standard',
                'archive' => false,
            ],
            [
                'product' => 'product_ecran_27_4k',
                'title' => 'Noir',
                'archive' => false,
            ],
            [
                'product' => 'product_ecran_27_4k',
                'title' => 'Blanc',
                'archive' => false,
            ],
            [
                'product' => 'product_souris_gaming_pro',
                'title' => 'Noir',
                'archive' => false,
            ],
            [
                'product' => 'product_souris_gaming_pro',
                'title' => 'Blanc',
                'archive' => false,
            ],
            [
                'product' => 'product_clavier_mecanique_rgb',
                'title' => 'AZERTY',
                'archive' => false,
            ],
            [
                'product' => 'product_clavier_mecanique_rgb',
                'title' => 'QWERTY',
                'archive' => false,
            ],
            [
                'product' => 'product_casque_audio_sans_fil',
                'title' => 'Noir',
                'archive' => false,
            ],
            [
                'product' => 'product_casque_audio_sans_fil',
                'title' => 'Blanc',
                'archive' => false,
            ],
            [
                'product' => 'product_webcam_hd',
                'title' => 'Standard',
                'archive' => false,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Small',
                'archive' => false,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Medium',
                'archive' => false,
            ],
            [
                'product' => 'product_tablette_graphique',
                'title' => 'Large',
                'archive' => false,
            ]
        ];

        foreach ($variants as $variantData) {
            $variant = new Variant();
            $variant->setProduct($this->getReference($variantData['product'], Product::class));
            $variant->setTitle($variantData['title']);
            $variant->setArchive($variantData['archive']);
            
            $manager->persist($variant);
            $this->addReference('variant_' . strtolower(str_replace(' ', '_', $variantData['product'] . '_' . $variantData['title'])), $variant);
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