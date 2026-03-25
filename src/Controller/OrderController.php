<?php

namespace App\Controller;

use App\Entity\Order; // Entité représentant une commande
use Dompdf\Dompdf;    // Bibliothèque pour générer des PDF
use Dompdf\Options;   // Options de configuration pour Dompdf
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response; // Classe pour retourner des réponses HTTP
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    // Route pour générer la facture PDF d'une commande spécifique
    #[Route('/order/{id}/invoice', name: 'order_invoice')]
    public function invoice(Order $order): Response
    {
        // Vérifie que l'utilisateur est bien connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Vérifie que l'utilisateur connecté est le propriétaire de la commande
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException(); // sinon accès refusé
        }

        // Génère le HTML de la facture via Twig
        $html = $this->renderView('order/invoice.html.twig', [
            'order' => $order
        ]);

        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial'); // police par défaut

        // Création de l'instance Dompdf avec les options
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);              // Charge le HTML
        $dompdf->setPaper('A4', 'portrait');   // Définit le format du papier
        $dompdf->render();                     // Génère le PDF

        // Récupère le contenu binaire du PDF
        $pdfContent = $dompdf->output();

        // Retourne la réponse HTTP contenant le PDF en pièce jointe
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf', // type MIME PDF
                'Content-Disposition' => 'attachment; filename="facture-'.$order->getId().'.pdf"', // téléchargement avec nom
            ]
        );
    }
}