<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer quelques utilisateurs de test
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setPseudo($faker->userName());
            $user->setPhone($faker->phoneNumber());
            $user->setNote($faker->sentence());
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password123'
            );
            $user->setHash($hashedPassword);

            $manager->persist($user);

            // Ajouter une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
} 