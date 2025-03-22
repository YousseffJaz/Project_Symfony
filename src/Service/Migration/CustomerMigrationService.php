<?php

namespace App\Service\Migration;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class CustomerMigrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function migrateCustomers(): void
    {
        $orderRepository = $this->entityManager->getRepository(Order::class);
        $customerRepository = $this->entityManager->getRepository(Customer::class);

        // Récupérer toutes les commandes
        $orders = $orderRepository->findAll();
        $processedEmails = [];

        foreach ($orders as $order) {
            $email = $order->getEmail();
            
            // Si nous n'avons pas encore traité ce client
            if (!isset($processedEmails[$email])) {
                // Vérifier si le client existe déjà
                $customer = $customerRepository->findOneBy(['email' => $email]);
                
                if (!$customer) {
                    // Créer un nouveau client
                    $customer = new Customer();
                    $customer->setFirstname($order->getFirstname());
                    $customer->setLastname($order->getLastname());
                    $customer->setEmail($email);
                    $customer->setAddress($order->getAddress());
                    $customer->setPhone($order->getPhone());
                    
                    $this->entityManager->persist($customer);
                }
                
                $processedEmails[$email] = $customer;
            }

            // Associer la commande au client
            $customer = $processedEmails[$email];
            $order->setCustomer($customer);
        }

        // Sauvegarder les changements
        $this->entityManager->flush();
    }
} 