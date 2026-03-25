<?php

namespace App\Repository;

use App\Entity\OrderItem; // Entité représentant un item d'une commande
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository; // Classe de base pour les repositories
use Doctrine\Persistence\ManagerRegistry; // Gestionnaire d'entités (EntityManager)

class OrderItemRepository extends ServiceEntityRepository
{
    // Constructeur : initialisation du repository avec l'EntityManager et l'entité ciblée
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Retourne les produits les plus vendus
     *
     * @param int $limit Nombre maximum de produits à retourner
     * @return array Tableau avec 'productName' et 'totalSold'
     */
    public function findTopProducts(int $limit = 5)
    {
        // Création d'une requête DQL
        return $this->createQueryBuilder('oi') // 'oi' = alias pour OrderItem
            ->select('oi.productName, SUM(oi.quantity) as totalSold') // Somme des quantités vendues
            ->groupBy('oi.productName') // Groupement par nom de produit
            ->orderBy('totalSold', 'DESC') // Tri décroissant pour avoir les plus vendus en premier
            ->setMaxResults($limit) // Limite le nombre de résultats
            ->getQuery() // Génère la requête
            ->getResult(); // Exécute et retourne les résultats
    }
}