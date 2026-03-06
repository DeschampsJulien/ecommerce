<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'account')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('account/index.html.twig');
    }
    
    #[Route('/account/orders', name: 'account_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $orders = $orderRepository->findBy(
            ['user' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/account/orders/{id}', name: 'account_order_show')]
    public function showOrder(
        int $id,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $order = $em->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        // sécurité : vérifier que la commande appartient à l'utilisateur
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }
}