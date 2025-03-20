<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tasks = [
            [
                'name' => 'Vérifier le stock des ordinateurs portables',
                'admin' => 'admin_test',
                'createdBy' => 'admin_user1',
                'complete' => false,
            ],
            [
                'name' => 'Commander des câbles USB Type-C',
                'admin' => 'admin_user1',
                'createdBy' => 'admin_test',
                'complete' => true,
                'completeBy' => 'admin_user1',
                'updatedAt' => new \DateTime('now', timezone_open('Europe/Paris')),
            ],
            [
                'name' => 'Mettre à jour les prix des smartphones',
                'admin' => 'admin_test',
                'createdBy' => 'admin_test',
                'complete' => false,
            ],
            [
                'name' => 'Vérifier les précommandes en attente',
                'admin' => 'admin_user1',
                'createdBy' => 'admin_user1',
                'complete' => false,
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->setName($taskData['name']);
            $task->setAdmin($this->getReference($taskData['admin'], Admin::class));
            $task->setCreatedBy($this->getReference($taskData['createdBy'], Admin::class));
            $task->setComplete($taskData['complete']);
            
            if (isset($taskData['completeBy'])) {
                $task->setCompleteBy($this->getReference($taskData['completeBy'], Admin::class));
            }
            
            if (isset($taskData['updatedAt'])) {
                $task->setUpdatedAt($taskData['updatedAt']);
            }
            
            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
        ];
    }
} 