<?php

namespace App\Controller;

use App\Repository\OrderRepository;         // Repository pour accéder aux commandes
use Doctrine\ORM\EntityManagerInterface;   // Pour gérer la persistance en base
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;  // Pour accéder aux données de la requête
use Symfony\Component\HttpFoundation\Response; // Pour renvoyer une réponse HTTP
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    // Route pour recevoir les webhooks Stripe
    // Méthode POST uniquement
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ): Response {

        // Récupère le contenu brut de la requête POST envoyé par Stripe
        $payload = $request->getContent();

        // Décode le JSON reçu en objet PHP
        $event = json_decode($payload);

        // Vérifie si l'événement correspond à un paiement réussi
        if ($event->type === 'payment_intent.succeeded') {

            // Récupère l'objet PaymentIntent
            $paymentIntent = $event->data->object;

            // Récupère l'ID de la commande stocké dans les métadonnées de Stripe
            $orderId = $paymentIntent->metadata->order_id;

            // Cherche la commande correspondante en base
            $order = $orderRepository->find($orderId);

            // Si la commande existe, met à jour son statut
            if ($order) {
                $order->setStatus('paid'); // change le statut à "paid"
                $em->flush();              // sauvegarde la modification en base
            }
        }

        // Renvoie une réponse 200 à Stripe pour confirmer la réception du webhook
        return new Response('Webhook handled', 200);
    }
}