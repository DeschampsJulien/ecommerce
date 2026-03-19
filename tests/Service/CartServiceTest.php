<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use App\Entity\Product;

class CartServiceTest extends TestCase
{
    private CartService $cartService;

    protected function setUp(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->cartService = new CartService($requestStack);
    }

    public function testAddProduct(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test');
        $product->method('getPrice')->willReturn(10.0); // ✅ float

        $this->cartService->add($product, 'M', 1);

        $cart = $this->cartService->getCart();

        $this->assertCount(1, $cart);
        $this->assertEquals(1, $cart['1_M']['quantity']);
    }

    public function testAddSameProductIncreasesQuantity(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test');
        $product->method('getPrice')->willReturn(10.0); // ✅ float

        $this->cartService->add($product, 'M', 1);
        $this->cartService->add($product, 'M', 2);

        $cart = $this->cartService->getCart();

        $this->assertEquals(3, $cart['1_M']['quantity']);
    }

    public function testRemoveProduct(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test');
        $product->method('getPrice')->willReturn(10.0); // ✅ float

        $this->cartService->add($product, 'M', 1);
        $this->cartService->remove('1_M');

        $cart = $this->cartService->getCart();
        $this->assertEmpty($cart);
    }

    public function testGetTotal(): void
    {
        $product1 = $this->createMock(Product::class);
        $product1->method('getId')->willReturn(1);
        $product1->method('getName')->willReturn('Test1');
        $product1->method('getPrice')->willReturn(10.0); // ✅ float

        $product2 = $this->createMock(Product::class);
        $product2->method('getId')->willReturn(2);
        $product2->method('getName')->willReturn('Test2');
        $product2->method('getPrice')->willReturn(20.0); // ✅ float

        $this->cartService->add($product1, 'M', 2); // 20€
        $this->cartService->add($product2, 'L', 1); // 20€

        $total = $this->cartService->getTotal();
        $this->assertEquals(40.0, $total);
    }
}