<?php

namespace App\Controller;

use App\Service\CartService;   // Service pour gérer le panier (ajout, suppression, total)
use App\Service\StripeService; // Service pour gérer les paiements Stripe
use App\Entity\Order;       // Entité représentant une commande
use App\Entity\OrderItem;   // Entité représentant un item d'une commande
use App\Repository\StockRepository;   // Pour récupérer/mettre à jour les stocks des produits
use App\Repository\ProductRepository; // Pour récupérer des produits
use Doctrine\ORM\EntityManagerInterface; // Pour persister, supprimer ou mettre à jour les entités
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Fournit les helpers render, redirect, etc.
use Symfony\Component\HttpFoundation\Response;                     // Pour retourner des réponses HTTP
use Symfony\Component\Routing\Attribute\Route;                     // Pour définir les routes en attributs

class CartController extends AbstractController
{
    // Affichage du panier
    #[Route('/cart', name: 'app_cart')]
    public function index(
        CartService $cartService,
        ProductRepository $productRepository
    ): Response {

        // Récupération du panier (stocké en session)
        $cart = $cartService->getCart();
        $cartData = [];
        $total = 0;

        // Parcours des produits du panier
        foreach ($cart as $key => $quantity) {

            // Format de la clé : "id_size" (ex: 12_M)
            $parts = explode('_', $key);
            $id = $parts[0];
            $size = $parts[1] ?? 'M'; // taille par défaut

            // Récupération du produit en base
            $product = $productRepository->find($id);

            if (!$product) {
                continue; // produit supprimé ignoré
            }

            // Gestion du format quantity (int ou tableau)
            $qty = is_array($quantity) ? (int)$quantity['quantity'] : (int)$quantity;

            // Préparation des données pour Twig
            $cartData[] = [
                'key' => $key,
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $qty,
                'size' => $size,
                'image' => $product->getImage(),
            ];

            // Calcul du total
            $total += $product->getPrice() * $qty;
        }

        // Envoi des données à la vue
        return $this->render('cart/index.html.twig', [
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    // Suppression d’un produit du panier
    #[Route('/cart/remove/{key}', name: 'cart_remove')]
    public function remove(string $key, CartService $cartService): Response
    {
        $cartService->remove($key); // suppression via le service
        return $this->redirectToRoute('app_cart');
    }

    // Validation de commande (checkout)
    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(
        CartService $cartService,
        ProductRepository $productRepository,
        StockRepository $stockRepository,
        EntityManagerInterface $em,
    ): Response {

        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cart = $cartService->getCart();

        // Vérifie si le panier est vide
        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart');
        }

        // Création de la commande
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setStatus('pending');

        $total = 0;

        // Parcours du panier
        foreach ($cart as $key => $quantity) {

            // Extraction id + taille
            $parts = explode('_', $key);
            $id = $parts[0];

            if (!isset($parts[1])) {
                throw new \Exception('Erreur panier : taille manquante');
            }

            $size = $parts[1];
            
            // Récupération produit
            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            // Quantité
            $qty = is_array($quantity) ? (int) $quantity['quantity'] : (int) $quantity;

            // Récupération du stock pour produit + taille
            $stock = $stockRepository->findOneBy([
                'product' => $product,
                'size' => $size
            ]);

            // Vérification du stock disponible
            if (!$stock || $stock->getQuantity() < $qty) {
                $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                return $this->redirectToRoute('app_cart');
            }

            // Décrémentation du stock
            $stock->setQuantity($stock->getQuantity() - $qty);

            // Création d’un item de commande
            $orderItem = new OrderItem();
            $orderItem->setProductName($product->getName());
            $orderItem->setPrice($product->getPrice());
            $orderItem->setQuantity($qty);
            $orderItem->setSize($size);

            // Association à la commande
            $order->addOrderItem($orderItem);

            // Calcul du total
            $total += $product->getPrice() * $qty;
        }

        // Enregistrement du total
        $order->setTotal($total);

        // Persistance en base
        $em->persist($order);
        $em->flush();

        // Vidage du panier
        $cartService->clear();

        // Message de confirmation
        $this->addFlash('success', 'Commande validée avec succès.');

        // Redirection vers les commandes utilisateur
        return $this->redirectToRoute('account_orders');
    }

    // Paiement via Stripe
    #[Route('/order/{id}/pay', name: 'order_pay')]
    public function payOrder(
        Order $order,
        StripeService $stripeService
    ): Response {

        // Vérifie si la commande est déjà payée
        if ($order->getStatus() === 'paid') {
            $this->addFlash('warning', 'Cette commande est déjà payée.');
            return $this->redirectToRoute('account_orders');
        }

        // Création du PaymentIntent Stripe
        $paymentIntent = $stripeService->createPaymentIntent(
            $order->getTotal(),
            'eur',
            [
                'order_id' => $order->getId()
            ]
        );

        // Envoi des données à la vue de paiement
        return $this->render('payment/stripe.html.twig', [
            'clientSecret' => $paymentIntent->client_secret,
            'order' => $order
        ]);
    }

    // Retour après paiement réussi
    #[Route('/payment/success/{id}', name: 'payment_success')]
    public function paymentSuccess(Order $order, EntityManagerInterface $em): Response
    {
        // Mise à jour du statut
        $order->setStatus('paid');

        // Sauvegarde
        $em->flush();

        // Message de succès
        $this->addFlash('success', 'Paiement effectué avec succès !');

        // Redirection vers les commandes
        return $this->redirectToRoute('account_orders');
    }
}