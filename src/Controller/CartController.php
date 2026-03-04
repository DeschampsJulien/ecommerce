<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function index(): Response
    {
        // Pour l’instant on fait juste un test
        return $this->render('cart/index.html.twig', [
            'message' => 'Page Panier en construction',
        ]);
    }
}