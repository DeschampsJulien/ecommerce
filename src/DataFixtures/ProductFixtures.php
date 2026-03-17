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
            ['name' => 'Blackbelt',   'price' => 29.90, 'image' => '1.jpeg',  'Featured' => true],
            ['name' => 'BlueBelt',    'price' => 29.90, 'image' => '2.jpeg',  'Featured' => false],
            ['name' => 'Street',      'price' => 34.50, 'image' => '3.jpeg',  'Featured' => false],
            ['name' => 'Pokeball',    'price' => 45.00, 'image' => '4.jpeg',  'Featured' => true],
            ['name' => 'PinkLady',    'price' => 29.90, 'image' => '5.jpeg',  'Featured' => false],
            ['name' => 'Snow',        'price' => 32.00, 'image' => '6.jpeg',  'Featured' => false],
            ['name' => 'Greyback',    'price' => 28.50, 'image' => '7.jpeg',  'Featured' => false],
            ['name' => 'BlueCloud',   'price' => 45.00, 'image' => '8.jpeg',  'Featured' => false],
            ['name' => 'BornInUsa',   'price' => 59.90, 'image' => '9.jpeg',  'Featured' => true],
            ['name' => 'GreenSchool', 'price' => 42.20, 'image' => '10.jpeg', 'Featured' => false],
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setImage($data['image']);
            $product->setFeatured($data['Featured']);

            $manager->persist($product);

            foreach ($sizes as $size) {
                $stock = new Stock();
                $stock->setSize($size);
                $stock->setQuantity(10);
                $stock->setProduct($product);

                $manager->persist($stock);
            }
        }

        $manager->flush();
    }
}