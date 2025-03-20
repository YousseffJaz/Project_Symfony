<?php

namespace App\DataFixtures;

use App\Entity\Upload;
use App\Entity\Folder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UploadFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques uploads de test
        for ($i = 0; $i < 10; $i++) {
            $upload = new Upload();
            $upload->setFilename($faker->word() . '.pdf');
            $upload->setName($faker->word() . '.pdf');
            
            // Assigner un dossier aléatoire
            $folder = $this->getReference('folder_' . $faker->numberBetween(0, 4), Folder::class);
            $upload->setFolder($folder);

            $manager->persist($upload);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FolderFixtures::class,
        ];
    }
} 