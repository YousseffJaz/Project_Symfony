<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Cette classe ne fait rien car les dépendances sont gérées par getDependencies()
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            ProductFixtures::class,
            AdminFixtures::class,
            OrderFixtures::class,
            OrderHistoryFixtures::class,
            TaskFixtures::class,
            NotificationFixtures::class,
            PriceListFixtures::class,
            VariantFixtures::class,
            PreorderFixtures::class,
            StockListFixtures::class,
            LineItemFixtures::class,
            NoteFixtures::class,
            FluxFixtures::class,
            TransactionFixtures::class,
        ];
    }
} 