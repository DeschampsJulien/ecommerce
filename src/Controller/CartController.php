<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\StripeService;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\StockRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    // #[Route('/cart', name: 'app_cart')]
    // public function index(CartService $cartService): Response
    // {
    //     return $this->render('cart/index.html.twig', [
    //         'cart' => $cartService->getCart(),
    //         'total' => $cartService->getTotal(),
    //     ]);
    // }

    #[Route('/cart', name: 'app_cart')]
    public function index(
        CartService $cartService,
        ProductRepository $productRepository
    ): Response {

        $cart = $cartService->getCart();
        $cartData = [];
        $total = 0;

        foreach ($cart as $key => $quantity) {

            $parts = explode('_', $key);
            $id = $parts[0];
            $size = $parts[1] ?? 'M';

            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            $qty = is_array($quantity) ? (int)$quantity['quantity'] : (int)$quantity;

            $cartData[] = [
                'key' => $key,
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $qty,
                'size' => $size,
                'image' => $product->getImage(), // 🔥 MAINTENANT ÇA MARCHE
            ];

            $total += $product->getPrice() * $qty;
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    #[Route('/cart/remove/{key}', name: 'cart_remove')]
    public function remove(string $key, CartService $cartService): Response
    {
        $cartService->remove($key);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(
        CartService $cartService,
        ProductRepository $productRepository,
        StockRepository $stockRepository,
        EntityManagerInterface $em,
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
        // foreach ($cart as $key => $quantity) {
        //     $parts = explode('_', $key);
        //     $id = $parts[0];
        //     $size = $parts[1] ?? 'M';

        //     $product = $productRepository->find($id);

        //     if (!$product) {
        //         continue;
        //     }

        //     // calcule quantité correcte
        //     $qty = is_array($quantity) ? (int) $quantity['quantity'] : (int) $quantity;
        //     $orderItem = new OrderItem();
        //     $orderItem->setProductName($product->getName());
        //     $orderItem->setPrice($product->getPrice());
        //     $orderItem->setQuantity($qty); // ✅ utilise $qty
        //     $orderItem->setSize($size);

        //     $order->addOrderItem($orderItem);
        //     $total += $product->getPrice() * $qty;
        // }

        foreach ($cart as $key => $quantity) {

            $parts = explode('_', $key);
            $id = $parts[0];
            // $size = $parts[1] ?? 'M';
            if (!isset($parts[1])) {
                throw new \Exception('Erreur panier : taille manquante');
            }

            $size = $parts[1];
            
            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            $qty = is_array($quantity) ? (int) $quantity['quantity'] : (int) $quantity;

            // 🔥 Récupération du stock
            $stock = $stockRepository->findOneBy([
                'product' => $product,
                'size' => $size
            ]);

            // ❌ Vérification du stock
            if (!$stock || $stock->getQuantity() < $qty) {
                $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                return $this->redirectToRoute('app_cart');
            }

            // 🔥 Décrémentation
            $stock->setQuantity($stock->getQuantity() - $qty);

            // 🧾 OrderItem
            $orderItem = new OrderItem();
            $orderItem->setProductName($product->getName());
            $orderItem->setPrice($product->getPrice());
            $orderItem->setQuantity($qty);
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

    #[Route('/order/{id}/pay', name: 'order_pay')]
    public function payOrder(
        Order $order,
        StripeService $stripeService
    ): Response {

        if ($order->getStatus() === 'paid') {
            $this->addFlash('warning', 'Cette commande est déjà payée.');
            return $this->redirectToRoute('account_orders');
        }

        $paymentIntent = $stripeService->createPaymentIntent(
            $order->getTotal(),
            'eur',
            [
                'order_id' => $order->getId()
            ]
        );

        return $this->render('payment/stripe.html.twig', [
            'clientSecret' => $paymentIntent->client_secret,
            'order' => $order
        ]);
    }

    #[Route('/payment/success/{id}', name: 'payment_success')]
    public function paymentSuccess(Order $order, EntityManagerInterface $em): Response
    {
        $order->setStatus('paid');
        $em->flush();

        $this->addFlash('success', 'Paiement effectué avec succès !');

        return $this->redirectToRoute('account_orders');
    }
}