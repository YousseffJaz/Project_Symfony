<?php

namespace App\DataFixtures;

use App\Entity\Note;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class NoteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques notes de test
        for ($i = 0; $i < 15; $i++) {
            $note = new Note();
            $note->setName($faker->sentence());
            $note->setAmount($faker->randomFloat(2, 100, 10000));

            $manager->persist($note);
            
            // Ajouter une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('note_' . $i, $note);
        }

        $manager->flush();
    }
} 