<?php

declare(strict_types=1);

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
            AdminFixtures::class,
            OrderFixtures::class,
            ProductFixtures::class,
        ];
    }
}
