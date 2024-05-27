<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['products'];
    }

    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setName("First Product");
        $product1->setPrice(1);
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setName("Second Product");
        $product2->setPrice(2.2);
        $manager->persist($product2);
        $product3 = new Product();

        $product3->setName("Third Product");
        $product3->setPrice(3.33);
        $manager->persist($product3);

        $manager->flush();
    }
}
