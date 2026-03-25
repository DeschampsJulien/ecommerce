<?php

namespace App\DataFixtures;

use App\Entity\Product; // Entité représentant un produit
use App\Entity\Stock;   // Entité représentant le stock d'un produit par taille
use Doctrine\Bundle\FixturesBundle\Fixture; // Classe de base pour les fixtures
use Doctrine\Persistence\ObjectManager;     // Pour gérer l'insertion en base

class ProductFixtures extends Fixture
{
    // Méthode appelée par Doctrine Fixtures pour insérer les données
    public function load(ObjectManager $manager): void
    {
        // Tableau de produits prédéfinis
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

        // Tailles disponibles pour chaque produit
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        // Boucle sur chaque produit pour créer l'entité Product
        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);       // Nom du produit
            $product->setPrice($data['price']);     // Prix du produit
            $product->setImage($data['image']);     // Nom de l'image
            $product->setFeatured($data['Featured']); // Produit mis en avant ou non

            $manager->persist($product); // Prépare le produit pour insertion

            // Boucle sur chaque taille pour créer le stock
            foreach ($sizes as $size) {
                $stock = new Stock();
                $stock->setSize($size);         // Taille du stock
                $stock->setQuantity(5);         // Quantité initiale pour chaque taille
                $stock->setProduct($product);   // Associe le stock au produit

                $manager->persist($stock); // Prépare le stock pour insertion
            }
        }

        // Exécute toutes les insertions en base
        $manager->flush();
    }
}