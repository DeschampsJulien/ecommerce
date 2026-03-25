<?php

namespace App\Controller;

use App\Repository\ProductRepository; // Pour accéder aux produits en base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Fournit des helpers : render, redirect, addFlash, etc.
use Symfony\Component\HttpFoundation\Response;                     // Représente une réponse HTTP (HTML, JSON, PDF, etc.)
use Symfony\Component\Routing\Attribute\Route;                     // Permet de définir des routes directement sur les méthodes

class HomeController extends AbstractController
{
    // Définition de la route pour la page d'accueil "/"
    // Le nom de la route est "app_home"
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupère tous les produits "featured" (mis en avant) depuis la base
        $featuredProducts = $productRepository->findBy([
            'featured' => true
        ]);

        // Retourne une réponse HTML en affichant le template Twig
        // et en lui passant les produits en paramètre
        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}