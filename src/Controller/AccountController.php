<?php

namespace App\Controller;

use App\Entity\Order; // Entité représentant une commande
use Doctrine\ORM\EntityManagerInterface; // Pour accéder à la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    // Affichage de la page "Mon compte"
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Rend la vue du compte
        return $this->render('account/index.html.twig');
    }

    // Affichage de la liste des commandes de l'utilisateur
    #[Route('/account/orders', name: 'account_orders')]
    public function orders(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération des commandes de l'utilisateur, triées par ID décroissant
        $orders = $em->getRepository(Order::class)->findBy(
            ['user' => $this->getUser()],
            ['id' => 'DESC']
        );

        // Rend la vue des commandes
        return $this->render('account/orders.html.twig', [
            'orders' => $orders
        ]);
    }

    // Affichage du détail d'une commande spécifique
    #[Route('/account/orders/{id}', name: 'account_order_show')]
    public function showOrder(int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Recherche de la commande par ID
        $order = $em->getRepository(Order::class)->find($id);

        // Gestion commande introuvable
        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        // Vérifie que la commande appartient bien à l'utilisateur connecté
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Rend la vue du détail de la commande
        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }
}