<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Notification;
use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $notifications = [
            [
                'admin' => 'admin_test',
                'invoice' => 'order_1',
                'seen' => false,
            ],
            [
                'admin' => 'admin_test',
                'invoice' => 'order_2',
                'seen' => true,
            ],
            [
                'admin' => 'admin_user1',
                'invoice' => 'order_1',
                'seen' => false,
            ],
            [
                'admin' => 'admin_user1',
                'invoice' => 'order_2',
                'seen' => false,
            ],
        ];

        foreach ($notifications as $notificationData) {
            $notification = new Notification();
            $notification->setAdmin($this->getReference($notificationData['admin'], Admin::class));
            $notification->setInvoice($this->getReference($notificationData['invoice'], Order::class));
            $notification->setSeen($notificationData['seen']);
            
            $manager->persist($notification);
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