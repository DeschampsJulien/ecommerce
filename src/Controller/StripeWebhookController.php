<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ): Response {

        $payload = $request->getContent();
        $event = json_decode($payload);

        if ($event->type === 'payment_intent.succeeded') {

            $paymentIntent = $event->data->object;
            $orderId = $paymentIntent->metadata->order_id;

            $order = $orderRepository->find($orderId);

            if ($order) {
                $order->setStatus('paid');
                $em->flush();
            }
        }

        return new Response('Webhook handled', 200);
    }
}