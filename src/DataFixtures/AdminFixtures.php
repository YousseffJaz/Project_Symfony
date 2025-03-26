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
                'firstName' => 'Admin',
                'lastName' => 'System',
                'email' => 'admin@example.com',
                'phone' => '0000000000',
                'password' => 'Admin123!@#',
                'role' => 'ROLE_SUPER_ADMIN',
            ],
            [
                'firstName' => 'Manager',
                'lastName' => 'System',
                'email' => 'manager@example.com',
                'phone' => '0000000001',
                'password' => 'Manager123!@#',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'firstName' => 'Staff',
                'lastName' => 'System',
                'email' => 'staff@example.com',
                'phone' => '0000000002',
                'password' => 'Staff123!@#',
                'role' => 'ROLE_ADMIN',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = new Admin();
            $admin->setFirstName($adminData['firstName']);
            $admin->setLastName($adminData['lastName']);
            $admin->setEmail($adminData['email']);
            $admin->setPhone($adminData['phone']);
            $admin->setRole($adminData['role']);
            
            $hashedPassword = $this->passwordHasher->hashPassword(
                $admin,
                $adminData['password']
            );
            $admin->setHash($hashedPassword);
            
            $manager->persist($admin);
        }

        $manager->flush();
    }
} 