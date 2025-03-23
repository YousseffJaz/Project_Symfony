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
                'lastName' => 'Admin',
                'email' => 'test@gmail.com',
                'phone' => '0123456789',
                'password' => 'testtest',
                'role' => 'ROLE_SUPER_ADMIN',
            ],
            [
                'firstName' => 'User1',
                'lastName' => 'Manager',
                'email' => 'user1@example.com',
                'phone' => '0123456790',
                'password' => 'user123',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'firstName' => 'User2',
                'lastName' => 'Staff',
                'email' => 'user2@example.com',
                'phone' => '0123456791',
                'password' => 'user123',
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
            
            $hashedPassword = $this->passwordHasher->hashPassword($admin, $adminData['password']);
            $admin->setHash($hashedPassword);
            
            $manager->persist($admin);
            $this->addReference('admin_' . strtolower($adminData['firstName']), $admin);
        }

        $manager->flush();
    }
} 