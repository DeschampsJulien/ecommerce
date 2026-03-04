<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Stock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $productsData = [
            ['Blackbelt', 29.90, true],
            ['Pokeball', 45, true],
            ['BornInUsa', 59.90, true],
            ['BlueBelt', 29.90, false],
            ['Street', 34.50, false],
            ['PinkLady', 29.90, false],
            ['Snow', 32, false],
            ['Greyback', 28.50, false],
            ['BlueCloud', 45, false],
            ['GreenSchool', 42.20, false],
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        foreach ($productsData as $data) {

            $product = new Product();
            $product->setName($data[0]);
            $product->setPrice($data[1]);
            $product->setFeatured($data[2]);

            $manager->persist($product);

            foreach ($sizes as $size) {
                $stock = new Stock();
                $stock->setSize($size);
                $stock->setQuantity(5);
                $stock->setProduct($product);

                $manager->persist($stock);
            }
        }

        $manager->flush();
    }
}