<?php

namespace App\DataFixtures;

use App\Entity\Folder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FolderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques dossiers de test
        for ($i = 0; $i < 5; $i++) {
            $folder = new Folder();
            $folder->setName($faker->word());
            $folder->setType($faker->numberBetween(1, 3));

            $manager->persist($folder);
            
            // Ajouter une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('folder_' . $i, $folder);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
} 