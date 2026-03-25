<?php

namespace App\Repository;

use App\Entity\Order; // Entité représentant une commande
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository; // Classe de base pour les repositories Doctrine
use Doctrine\Persistence\ManagerRegistry; // Gestionnaire d'entités (EntityManager)

class OrderRepository extends ServiceEntityRepository
{
    // Constructeur : initialise le repository avec l'EntityManager et l'entité ciblée
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // ⚡ Ici, aucune méthode personnalisée n'est définie
    // On utilise les méthodes standard héritées de ServiceEntityRepository :
    // find(), findAll(), findBy(), findOneBy(), etc.
}