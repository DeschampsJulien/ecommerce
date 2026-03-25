<?php

namespace App\Entity;

use App\Repository\OrderItemRepository; // Repository pour gérer les OrderItem
use Doctrine\ORM\Mapping as ORM;        // Pour les attributs Doctrine

// Déclare l'entité OrderItem et son repository associé
#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column]
    private ?int $id = null; // Identifiant unique de l'item

    // Relation ManyToOne vers Order
    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)] // Clé étrangère obligatoire
    private ?Order $order = null; // Commande à laquelle appartient cet item

    #[ORM\Column(length: 255)]
    private ?string $productName = null; // Nom du produit au moment de la commande

    #[ORM\Column]
    private ?float $price = null; // Prix unitaire du produit au moment de la commande

    #[ORM\Column]
    private ?int $quantity = null; // Quantité commandée

    #[ORM\Column(length: 10)]
    private ?string $size = null; // Taille commandée (XS, S, M, L, XL)

    // ==========================
    // GETTERS & SETTERS
    // ==========================

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getOrder(): ?Order 
    { 
        return $this->order; 
    }

    public function setOrder(?Order $order): static 
    { 
        $this->order = $order; 
        return $this; 
    }

    public function getProductName(): ?string 
    { 
        return $this->productName; 
    }

    public function setProductName(string $productName): static 
    { 
        $this->productName = $productName; 
        return $this; 
    }

    public function getPrice(): ?float 
    { 
        return $this->price; 
    }

    public function setPrice(float $price): static 
    { 
        $this->price = $price; 
        return $this; 
    }

    public function getQuantity(): ?int 
    { 
        return $this->quantity; 
    }

    public function setQuantity(int $quantity): static 
    { 
        $this->quantity = $quantity; 
        return $this; 
    }

    public function getSize(): ?string 
    { 
        return $this->size; 
    }

    public function setSize(string $size): static 
    { 
        $this->size = $size; 
        return $this; 
    }
}