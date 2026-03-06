<?php

namespace App\Controller;

use App\Service\CartService;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/cart/remove/{key}', name: 'cart_remove')]
    public function remove(string $key, CartService $cartService): Response
    {
        $cartService->remove($key);

        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(
        CartService $cartService,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cart = $cartService->getCart();

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setStatus('pending');

        $total = 0;

        foreach ($cart as $key => $quantity) {

            // [$id, $size] = explode('_', $key);
            $parts = explode('_', $key);

            $id = $parts[0];
            $size = $parts[1] ?? 'M';

            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            $qty = is_array($quantity) ? (int) $quantity['quantity'] : (int) $quantity;

            $orderItem = new OrderItem();
            $orderItem->setProductName($product->getName());
            $orderItem->setPrice($product->getPrice());
            $orderItem->setQuantity((int)$quantity);
            $orderItem->setSize($size);

            $order->addOrderItem($orderItem);

            $total += $product->getPrice() * $qty;
        }

        $order->setTotal($total);

        $em->persist($order);
        $em->flush();

        $cartService->clear();

        $this->addFlash('success', 'Commande validée avec succès.');

        return $this->redirectToRoute('account_orders');
    }
}