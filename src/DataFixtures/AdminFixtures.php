<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admins = [
            [
                'firstName' => 'Test',
                'email' => 'test@gmail.com',
                'password' => 'testtest',
                'role' => 'ROLE_SUPER_ADMIN',
            ],
            [
                'firstName' => 'User1',
                'email' => 'user1@example.com',
                'password' => 'user123',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'firstName' => 'User2',
                'email' => 'user2@example.com',
                'password' => 'user123',
                'role' => 'ROLE_ADMIN',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = new Admin();
            $admin->setFirstName($adminData['firstName']);
            $admin->setEmail($adminData['email']);
            $admin->setRole($adminData['role']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($admin, $adminData['password']);
            $admin->setHash($hashedPassword);
            
            $manager->persist($admin);
            $this->addReference('admin_' . strtolower($adminData['firstName']), $admin);
        }

        $manager->flush();
    }
} 