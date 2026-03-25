<?php

namespace App\Entity;

use App\Repository\UserRepository; // Repository pour gérer les utilisateurs
use Doctrine\Common\Collections\ArrayCollection; // Collection pour les relations OneToMany
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM; // Annotations/Attributes pour Doctrine
use Symfony\Component\Security\Core\User\UserInterface; // Interface utilisateur Symfony
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface; // Interface pour les mots de passe

// Déclare cette classe comme entité gérée par Doctrine et le repository associé
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column] 
    private ?int $id = null; // Identifiant unique

    #[ORM\Column(length: 255)]
    private ?string $name = null; // Nom de l'utilisateur

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null; // Email unique (sert de login)

    #[ORM\Column]
    private array $roles = []; // Rôles de sécurité (ROLE_USER, ROLE_ADMIN, etc.)

    #[ORM\Column]
    private ?string $password = null; // Mot de passe hashé

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryAddress = null; // Adresse de livraison optionnelle

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
    private Collection $orders; // Relation OneToMany vers les commandes

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false; // Indique si l'email est vérifié

    public function __construct()
    {
        $this->roles = ['ROLE_USER']; // Rôle par défaut
        $this->orders = new ArrayCollection(); // Initialise la collection de commandes
    }

    // --- Getters / Setters ---

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string 
    { 
        return $this->email; 
    }

    public function setEmail(string $email): static 
    { 
        $this->email = $email; 
        return $this; 
    }

    // Identifiant utilisateur pour Symfony (ici, l'email)
    public function getUserIdentifier(): string 
    { 
        return (string)$this->email; 
    }

    // Retourne les rôles de l'utilisateur
    public function getRoles(): array 
    { 
        $roles = $this->roles; 
        $roles[] = 'ROLE_USER'; // Assure que ROLE_USER est toujours présent
        return array_unique($roles); 
    }

    public function setRoles(array $roles): static 
    { 
        $this->roles = $roles; 
        return $this; 
    }

    public function getPassword(): ?string 
    { 
        return $this->password; 
    }

    public function setPassword(string $password): static 
    { 
        $this->password = $password; 
        return $this; 
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    // Supprime les informations sensibles si nécessaire (pas utilisé ici)
    public function eraseCredentials(): void 
    {
    }

    // Retourne la collection de commandes associées à cet utilisateur
    public function getOrders(): Collection 
    { 
        return $this->orders; 
    }

    // Vérifie si l'utilisateur a confirmé son email
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }
}