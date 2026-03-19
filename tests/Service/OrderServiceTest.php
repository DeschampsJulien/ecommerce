<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    public function testCreateOrder(): void
    {
        $order = new Order();
        $product = new Product();
        $product->setName('TestProduct')->setPrice(20.0);

        $orderItem = new OrderItem();
        $orderItem->setProductName($product->getName())
                  ->setPrice($product->getPrice())
                  ->setQuantity(3)
                  ->setSize('M');

        $order->addOrderItem($orderItem);
        $order->setTotal($orderItem->getPrice() * $orderItem->getQuantity());

        $this->assertEquals('TestProduct', $orderItem->getProductName());
        $this->assertEquals(60.0, $order->getTotal());
        $this->assertEquals(3, $orderItem->getQuantity());
    }
}