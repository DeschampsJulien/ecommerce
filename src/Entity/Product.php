<?php

namespace App\Entity;

use App\Repository\ProductRepository; // Repository pour gérer les produits
use Doctrine\Common\Collections\ArrayCollection; // Collection pour OneToMany
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM; // Attributs Doctrine

// Déclare l'entité Product et son repository
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column]
    private ?int $id = null; // Identifiant unique

    #[ORM\Column(length: 255)]
    private ?string $name = null; // Nom du produit

    #[ORM\Column]
    private ?float $price = null; // Prix du produit

    #[ORM\Column(length: 255)]
    private ?string $image = null; // Nom de l'image associée

    #[ORM\Column]
    private ?bool $featured = false; // Produit mis en avant ou non

    // Relation OneToMany vers Stock
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Stock::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $stocks; // Collection des stocks associés

    public function __construct()
    {
        $this->stocks = new ArrayCollection(); // Initialise la collection de stocks
    }

    // ==========================
    // GETTERS & SETTERS
    // ==========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    // Ajoute un stock au produit
    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProduct($this); // Associe le stock à ce produit
        }

        return $this;
    }

    // Supprime un stock du produit
    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // Si le stock était associé à ce produit, on le dissocie
            if ($stock->getProduct() === $this) {
                $stock->setProduct(null);
            }
        }

        return $this;
    }
}