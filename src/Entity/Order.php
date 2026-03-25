<?php

namespace App\Entity;

use App\Repository\OrderRepository;       // Repository pour gérer les commandes
use Doctrine\Common\Collections\ArrayCollection; // Pour gérer les collections OneToMany
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;          // Attributs Doctrine
use App\Entity\OrderItem;                 // Entité représentant un item de commande
use App\Entity\User;                      // Entité représentant un utilisateur

// Déclare l'entité Order et son repository
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')] // Nom explicite car "order" est mot réservé SQL
class Order
{
    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column]
    private ?int $id = null; // Identifiant unique

    // Relation ManyToOne vers User
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)] // Obligatoire
    private ?User $user = null; // Utilisateur ayant passé la commande

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null; // Date de création

    #[ORM\Column]
    private ?float $total = null; // Montant total de la commande

    #[ORM\Column(length: 255)]
    private ?string $status = null; // Statut de la commande (pending, paid, cancelled...)

    // Relation OneToMany vers OrderItem
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $orderItems; // Liste des items de la commande

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Définit la date de création automatiquement
        $this->status = 'pending'; // Statut initial
        $this->orderItems = new ArrayCollection(); // Initialise la collection
    }

    // ==========================
    // GETTERS & SETTERS
    // ==========================
    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getTotal(): ?float { return $this->total; }
    public function setTotal(float $total): static { $this->total = $total; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    /**
     * Retourne la collection des items de la commande
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection { return $this->orderItems; }

    // Ajoute un item à la commande
    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this); // Associe l'item à cette commande
        }
        return $this;
    }

    // Supprime un item de la commande
    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // Si l'item est associé à cette commande, on le dissocie
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }
        return $this;
    }
}