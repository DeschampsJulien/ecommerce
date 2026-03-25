<?php

namespace App\Entity;

use App\Repository\StockRepository; // Repository pour gérer les stocks
use Doctrine\ORM\Mapping as ORM;     // Pour les attributs Doctrine

// Déclare l'entité Stock et son repository
#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column]
    private ?int $id = null; // Identifiant unique du stock

    #[ORM\Column(length: 10)]
    private ?string $size = null; // Taille du produit (XS, S, M, L, XL)

    #[ORM\Column]
    private ?int $quantity = null; // Quantité disponible pour cette taille

    #[ORM\ManyToOne(inversedBy: 'stocks')] // Relation ManyToOne avec Product
    #[ORM\JoinColumn(nullable: false)]      // Clé étrangère obligatoire
    private ?Product $product = null;        // Produit associé

    // ==========================
    // GETTERS & SETTERS
    // ==========================

    // Retourne l'identifiant du stock
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne la taille du stock
    public function getSize(): ?string
    {
        return $this->size;
    }

    // Définit la taille du stock
    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    // Retourne la quantité disponible
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    // Définit la quantité disponible
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    // Retourne le produit associé
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // Définit le produit associé
    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}