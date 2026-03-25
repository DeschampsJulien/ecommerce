# 🛍️ Ecommerce - Stubborn

Application e-commerce développée avec Symfony permettant la vente de sweat-shirts pour la marque **Stubborn**.

## 🚀 Fonctionnalités

### 👤 Utilisateur

* Inscription avec confirmation par email
* Connexion / Déconnexion
* Consultation des produits
* Filtrage par prix
* Ajout au panier avec choix de taille
* Gestion du panier (ajout, suppression, total)
* Validation de commande
* Paiement simulé avec Stripe

### 🛒 Panier

* Stocké en session
* Calcul automatique du total
* Gestion des quantités
* Suppression d’articles

### 📦 Commande

* Création d’une commande avec détails (Order / OrderItem)
* Calcul du total
* Décrémentation automatique du stock

### 🧑‍💼 Administration

* Accès sécurisé (ROLE_ADMIN)
* CRUD des produits
* Gestion du stock par taille (XS, S, M, L, XL)
* Dashboard avec statistiques :

  * Nombre de produits
  * Nombre de commandes
  * Chiffre d’affaires
  * Produits les plus vendus

---

## 🧰 Technologies utilisées

* PHP 8+
* Symfony
* Doctrine ORM
* MySQL
* Twig
* Stripe (mode test)
* PHPUnit

---

## ⚙️ Installation

```bash
# Cloner le projet
git clone https://github.com/ton-repo/ecommerce.git

cd ecommerce

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env .env.local

# Modifier les accès DB dans .env.local
DATABASE_URL="mysql://root:password@127.0.0.1:3306/ecommerce"

# STRIPE_SECRET_KEY=sk_test_123456789 dans .env.local

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données
php bin/console doctrine:fixtures:load
```

---

## ▶️ Lancer le projet

```bash
symfony server:start
```

👉 Accès : http://localhost:8000

---

## 🔐 Comptes de test

### Admin

* Email : [admin@stubborn.com](mailto:admin@stubborn.com)
* Mot de passe : admin123

### Utilisateur

* Inscription via le site

---

## 💳 Paiement Stripe

* Mode sandbox (test)
* Carte test :

```
4242 4242 4242 4242
```

* Date : n’importe quelle date future
* CVC : 123

---

## 🧪 Tests

Lancer les tests unitaires :

```bash
php bin/phpunit
```

Tests disponibles :

* Panier (ajout, suppression, total)
* Commande (création, calcul total)
* Achat complet (stock + commande)

---

## 📁 Structure du projet

```
src/
 ├── Controller/
 ├── Entity/
 ├── Repository/
 ├── Service/
 ├── Security/
templates/
tests/
```

---

## 🔒 Sécurité

* Authentification avec Symfony Security
* Rôles :

  * ROLE_USER
  * ROLE_ADMIN
* Protection des routes sensibles

---

## 📊 Dashboard Admin

Accessible via `/admin`

Permet de :

* Visualiser les statistiques
* Gérer les produits
* Modifier les stocks

---

## 🧠 Améliorations possibles

* Webhook Stripe
* Email de confirmation de commande
* Pagination des produits
* Interface UI/UX améliorée
* Gestion des promotions

---

## 👨‍💻 Auteur

Projet réalisé dans le cadre d’un TP Symfony.

---

## 🏁 Conclusion

Application e-commerce complète respectant les exigences du cahier des charges avec :

* Gestion des utilisateurs
* Système de commande
* Paiement simulé
* Interface d’administration

---
