<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderHistoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $orderHistories = [
            [
                'invoice' => 'order_1',
                'admin' => 'admin_admin',
                'title' => 'Commande créée',
            ],
            [
                'invoice' => 'order_1',
                'admin' => 'admin_admin',
                'title' => 'Commande validée',
            ],
            [
                'invoice' => 'order_2',
                'admin' => 'admin_manager',
                'title' => 'Commande créée',
            ],
            [
                'invoice' => 'order_2',
                'admin' => 'admin_manager',
                'title' => 'Commande en cours de traitement',
            ],
        ];

        foreach ($orderHistories as $historyData) {
            $history = new OrderHistory();
            $history->setInvoice($this->getReference($historyData['invoice'], Order::class));
            $history->setAdmin($this->getReference($historyData['admin'], Admin::class));
            $history->setTitle($historyData['title']);
            
            $manager->persist($history);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            AdminFixtures::class,
        ];
    }
} 