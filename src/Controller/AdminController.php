<?php

namespace App\Controller;

use App\Entity\Product; // Entité représentant un produit
use App\Entity\Stock;   // Entité représentant le stock d'un produit (taille/quantité)
use App\Form\ProductType; // Formulaire pour ajouter ou éditer un produit
use App\Repository\ProductRepository;   // Pour récupérer les produits depuis la base
use App\Repository\OrderRepository;     // Pour récupérer les commandes
use App\Repository\OrderItemRepository; // Pour récupérer les items des commandes
use Doctrine\ORM\EntityManagerInterface; // Pour persister, supprimer ou mettre à jour les entités
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;  // Représente une requête HTTP
use Symfony\Component\HttpFoundation\Response; // Représente une réponse HTTP
use Symfony\Component\Routing\Attribute\Route; // Pour définir les routes directement en attributs


// Définition du préfixe de route pour toutes les actions de ce controller
#[Route('/admin')]
class AdminController extends AbstractController
{
    // Route principale de l'administration (liste des produits)
    #[Route('/', name: 'app_admin')]
    public function index(ProductRepository $productRepository): Response
    {
        // Vérifie que l'utilisateur est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupère tous les produits depuis la base de données
        $products = $productRepository->findAll();

        // Rend le template admin/index.html.twig avec les produits
        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'controller_name' => 'AdminController'
        ]);
    }

    // Route du tableau de bord admin
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository
    ): Response {

        // Vérifie que l'utilisateur est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupère toutes les commandes
        $orders = $orderRepository->findAll();

        // Calcul du chiffre d'affaires total
        $totalRevenue = array_sum(array_map(
            fn($o) => $o->getTotal(), // récupère le total de chaque commande
            $orders
        ));

        // Rend le template admin/dashboard.html.twig avec les statistiques
        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => count($productRepository->findAll()), // nombre total de produits
            'totalOrders' => count($orders), // nombre total de commandes
            'totalRevenue' => $totalRevenue, // chiffre d'affaires total
            'topProducts' => $orderItemRepository->findTopProducts() // produits les plus vendus
        ]);
    }

    // Route pour créer un nouveau produit
    #[Route('/product/new', name: 'admin_product_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Product(); // nouvelle entité produit
        $form = $this->createForm(ProductType::class, $product); // création du formulaire

        $form->handleRequest($request); // traitement de la requête POST

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product); // prépare le produit pour insertion en DB

            // Ajouter stock par défaut pour toutes les tailles
            foreach (['XS','S','M','L','XL'] as $size) {
                $stock = new Stock(); // nouvelle entité stock
                $stock->setSize($size); // définit la taille
                $stock->setQuantity(2); // stock initial à 2
                $stock->setProduct($product); // associe le stock au produit
                $em->persist($stock); // prépare le stock pour insertion en DB
            }

            $em->flush(); // exécute toutes les insertions

            $this->addFlash('success', 'Produit ajouté avec succès.'); // message flash

            return $this->redirectToRoute('app_admin'); // redirection vers la liste des produits
        }

        // Rend le formulaire de création si non soumis ou invalide
        return $this->render('admin/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // Route pour éditer un produit existant
    #[Route('/product/{id}/edit', name: 'admin_product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProductType::class, $product); // formulaire pré-rempli
        $form->handleRequest($request); // traitement POST

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // enregistre les modifications
            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('app_admin'); // redirection vers la liste
        }

        // Rend le formulaire d'édition
        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    // Route pour supprimer un produit
    #[Route('/product/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($product); // supprime le produit
        $em->flush(); // applique la suppression

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_admin'); // redirection vers la liste
    }

    // Route pour mettre à jour le stock d'un produit
    #[Route('/stock/{id}', name: 'admin_stock_update', methods: ['POST'])]
    public function updateStock(Stock $stock, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $quantity = (int) $request->request->get('quantity'); // récupère la quantité depuis le formulaire
        $stock->setQuantity($quantity); // met à jour la quantité

        $em->flush(); // enregistre la modification

        // redirige vers l'édition du produit correspondant
        return $this->redirectToRoute('admin_product_edit', [
            'id' => $stock->getProduct()->getId()
        ]);
    }
}