<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Product;

class CartService
{
    private const CART_KEY = 'cart';

    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->session->get(self::CART_KEY, []);
    }

    public function add(Product $product, string $size, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $key = $product->getId() . '-' . $size;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'size' => $size,
                'quantity' => $quantity
            ];
        }

        $this->session->set(self::CART_KEY, $cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->session->set(self::CART_KEY, $cart);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->getCart() as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    public function clear(): void
    {
        $this->session->remove('cart');
    }
}