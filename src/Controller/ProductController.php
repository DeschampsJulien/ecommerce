<?php

namespace App\Controller;

use App\Entity\Product;             // Entité représentant un produit
use App\Repository\ProductRepository; // Repository pour interagir avec la table Product
use App\Service\CartService;        // Service pour gérer le panier
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;  // Pour récupérer les données HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    // Route pour afficher tous les produits, avec possibilité de filtrer par prix
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        // Récupère la plage de prix depuis les paramètres GET (ex: ?price=10-29)
        $priceRange = $request->query->get('price');

        $criteria = [];
        if ($priceRange) {
            // Définition des limites min/max selon la plage sélectionnée
            switch ($priceRange) {
                case '10-29':
                    $criteria['min'] = 10; $criteria['max'] = 29; break;
                case '29-35':
                    $criteria['min'] = 29; $criteria['max'] = 35; break;
                case '35-50':
                    $criteria['min'] = 35; $criteria['max'] = 50; break;
            }
        }

        // Si un critère de prix est défini, filtre les produits avec QueryBuilder
        if (!empty($criteria)) {
            $products = $productRepository->createQueryBuilder('p')
                ->where('p.price >= :min')
                ->andWhere('p.price <= :max')
                ->setParameter('min', $criteria['min'])
                ->setParameter('max', $criteria['max'])
                ->getQuery()
                ->getResult();
        } else {
            // Sinon récupère tous les produits
            $products = $productRepository->findAll();
        }

        // Rend la page avec les produits et la plage de prix sélectionnée
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'selectedRange' => $priceRange,
        ]);
    }

    // Route pour afficher un produit spécifique
    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product, Request $request, CartService $cartService): Response
    {
        // Si le formulaire POST est soumis pour ajouter le produit au panier
        if ($request->isMethod('POST')) {
            $size = $request->request->get('size'); // taille sélectionnée
            $quantity = (int) $request->request->get('quantity', 1); // quantité sélectionnée, par défaut 1

            // Appelle le service CartService pour ajouter le produit au panier
            $cartService->add($product, $size, $quantity);

            // Message flash pour confirmer l'ajout
            $this->addFlash('success', 'Produit ajouté au panier !');

            // Redirection vers la page du panier
            return $this->redirectToRoute('app_cart');
        }

        // Si GET, affiche la page du produit
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}