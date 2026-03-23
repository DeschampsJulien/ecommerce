<?php

namespace App\Controller;

use App\Entity\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/{id}/invoice', name: 'order_invoice')]
    public function invoice(Order $order): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Sécurité (empêche accès aux autres commandes)
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // HTML Twig
        $html = $this->renderView('order/invoice.html.twig', [
            'order' => $order
        ]);

        // Config PDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->stream('facture-'.$order->getId().'.pdf', [
                "Attachment" => true
            ]),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
}