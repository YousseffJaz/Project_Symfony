<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'name' => 'Electronique',
            ],
            [
                'name' => 'Informatique',
            ],
            [
                'name' => 'Accessoires',
            ],
            [
                'name' => 'Composants',
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);

            $manager->persist($category);
            $this->addReference('category_'.strtolower(str_replace(' ', '_', $categoryData['name'])), $category);
        }

        $manager->flush();
    }
}
