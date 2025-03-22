<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $customers = [
            [
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'phone' => '0123456789',
                'address' => '123 Rue de Paris, 75001 Paris, France'
            ],
            [
                'firstname' => 'Marie',
                'lastname' => 'Martin',
                'email' => 'marie.martin@example.com',
                'phone' => '0234567890',
                'address' => '456 Avenue des Champs-Élysées, 75008 Paris, France'
            ],
            [
                'firstname' => 'Pierre',
                'lastname' => 'Bernard',
                'email' => 'pierre.bernard@example.com',
                'phone' => '0345678901',
                'address' => '789 Boulevard Saint-Germain, 75006 Paris, France'
            ],
            [
                'firstname' => 'Sophie',
                'lastname' => 'Dubois',
                'email' => 'sophie.dubois@example.com',
                'phone' => '0456789012',
                'address' => '12 Rue de la République, 69001 Lyon, France'
            ],
            [
                'firstname' => 'Lucas',
                'lastname' => 'Petit',
                'email' => 'lucas.petit@example.com',
                'phone' => '0567890123',
                'address' => '45 Avenue Jean Jaurès, 31000 Toulouse, France'
            ],
            [
                'firstname' => 'Emma',
                'lastname' => 'Leroy',
                'email' => 'emma.leroy@example.com',
                'phone' => '0678901234',
                'address' => '78 Rue de la Liberté, 33000 Bordeaux, France'
            ],
            [
                'firstname' => 'Thomas',
                'lastname' => 'Moreau',
                'email' => 'thomas.moreau@example.com',
                'phone' => '0789012345',
                'address' => '23 Boulevard Victor Hugo, 59000 Lille, France'
            ],
            [
                'firstname' => 'Lea',
                'lastname' => 'Roux',
                'email' => 'lea.roux@example.com',
                'phone' => '0890123456',
                'address' => '56 Avenue Foch, 67000 Strasbourg, France'
            ],
            [
                'firstname' => 'Hugo',
                'lastname' => 'Simon',
                'email' => 'hugo.simon@example.com',
                'phone' => '0901234567',
                'address' => '89 Rue Nationale, 44000 Nantes, France'
            ],
            [
                'firstname' => 'Chloe',
                'lastname' => 'Michel',
                'email' => 'chloe.michel@example.com',
                'phone' => '0612345678',
                'address' => '34 Boulevard de la Mer, 06000 Nice, France'
            ],
            [
                'firstname' => 'Antoine',
                'lastname' => 'Laurent',
                'email' => 'antoine.laurent@example.com',
                'phone' => '0723456789',
                'address' => '67 Rue des Fleurs, 13001 Marseille, France'
            ],
            [
                'firstname' => 'Julie',
                'lastname' => 'Garcia',
                'email' => 'julie.garcia@example.com',
                'phone' => '0834567890',
                'address' => '90 Avenue de la Paix, 38000 Grenoble, France'
            ],
            [
                'firstname' => 'Nicolas',
                'lastname' => 'David',
                'email' => 'nicolas.david@example.com',
                'phone' => '0945678901',
                'address' => '12 Rue du Commerce, 35000 Rennes, France'
            ],
            [
                'firstname' => 'Sarah',
                'lastname' => 'Bertrand',
                'email' => 'sarah.bertrand@example.com',
                'phone' => '0756789012',
                'address' => '45 Boulevard Gambetta, 21000 Dijon, France'
            ],
            [
                'firstname' => 'Maxime',
                'lastname' => 'Robert',
                'email' => 'maxime.robert@example.com',
                'phone' => '0867890123',
                'address' => '78 Rue de la Gare, 54000 Nancy, France'
            ]
        ];

        foreach ($customers as $customerData) {
            $customer = new Customer();
            $customer->setFirstname($customerData['firstname']);
            $customer->setLastname($customerData['lastname']);
            $customer->setEmail($customerData['email']);
            $customer->setPhone($customerData['phone']);
            $customer->setAddress($customerData['address']);
            
            $manager->persist($customer);
            $reference = 'customer_' . strtolower($customerData['firstname'] . '_' . $customerData['lastname']);
            $this->addReference($reference, $customer);
        }

        $manager->flush();
    }
} 