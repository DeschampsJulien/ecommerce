<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Order;
use App\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    public function testFullPurchaseProcess(): void
    {
        // 🧱 1. Produit
        $product = new Product();
        $product->setName('Blackbelt');
        $product->setPrice(30);

        // 🧱 2. Stock
        $stock = new Stock();
        $stock->setSize('M');
        $stock->setQuantity(5);
        $stock->setProduct($product);

        // 🧱 3. Simulation panier
        $cartItem = [
            'product' => $product,
            'size' => 'M',
            'quantity' => 2
        ];

        // 🧱 4. Création commande
        $order = new Order();

        $orderItem = new OrderItem();
        $orderItem->setProductName($product->getName());
        $orderItem->setPrice($product->getPrice());
        $orderItem->setQuantity($cartItem['quantity']);
        $orderItem->setSize($cartItem['size']);

        $order->addOrderItem($orderItem);

        // 🧱 5. Calcul total
        $total = $product->getPrice() * $cartItem['quantity'];
        $order->setTotal($total);

        // 🧱 6. Décrément stock
        $stock->setQuantity(
            $stock->getQuantity() - $cartItem['quantity']
        );

        // ✅ ASSERTIONS

        // total commande
        $this->assertEquals(60, $order->getTotal());

        // quantité commandée
        $this->assertEquals(2, $orderItem->getQuantity());

        // stock décrémenté
        $this->assertEquals(3, $stock->getQuantity());
    }
}