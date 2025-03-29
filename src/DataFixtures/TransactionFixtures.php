<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $usedOrders = [];

        // Créer quelques transactions de test
        for ($i = 0; $i < 15; ++$i) {
            $transaction = new Transaction();
            $transaction->setAmount($faker->randomFloat(2, -1000, 1000));
            $transaction->setComment($faker->sentence());

            // Assigner une commande aléatoire (si disponible)
            if ($faker->boolean() && count($usedOrders) < 3) {
                do {
                    $orderNumber = $faker->numberBetween(1, 3);
                } while (in_array($orderNumber, $usedOrders, true));

                $usedOrders[] = $orderNumber;
                $order = $this->getReference('order_'.$orderNumber, Order::class);
                $transaction->setInvoice($order);
            }

            $manager->persist($transaction);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
        ];
    }
}
