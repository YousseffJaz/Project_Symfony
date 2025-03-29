<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Flux;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FluxFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques flux de test
        for ($i = 0; $i < 8; ++$i) {
            $flux = new Flux();
            $flux->setName($faker->word());
            $flux->setAmount($faker->randomFloat(2, 100, 10000));
            $flux->setType($faker->boolean());

            $manager->persist($flux);
        }

        $manager->flush();
    }
}
