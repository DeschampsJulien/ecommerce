<?php

namespace App\DataFixtures;

use App\Entity\User; // Entité représentant un utilisateur
use Doctrine\Bundle\FixturesBundle\Fixture; // Classe de base pour les fixtures Doctrine
use Doctrine\Persistence\ObjectManager; // Pour gérer l’insertion en base
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Pour hasher les mots de passe

class AdminUserFixtures extends Fixture
{
    // Service pour hasher le mot de passe
    private UserPasswordHasherInterface $passwordHasher;

    // Injection du service via le constructeur
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // Méthode qui sera appelée par Doctrine Fixtures pour charger les données
    public function load(ObjectManager $manager): void
    {
        // Création d’un nouvel utilisateur admin
        $admin = new User();
        $admin->setName('Admin'); // Nom de l’utilisateur
        $admin->setEmail('admin@stubborn.com'); // Email de connexion
        $admin->setRoles(['ROLE_ADMIN']); // Rôle admin
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123') // Hash du mot de passe
        );

        // Prépare l’entité pour insertion
        $manager->persist($admin);

        // Exécute l’insertion en base
        $manager->flush();
    }
}