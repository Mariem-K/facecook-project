<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // // create 5 categories
        // for ($i = 0; $i < 5; $i++) {
        //     $category = new Category();
        //     $category->setName('catégorie' . $i);
        //     $manager->persist($category);
        // }

        // // create 10 ingredients
        // for ($i = 0;)


        $manager->flush();
    }
}
