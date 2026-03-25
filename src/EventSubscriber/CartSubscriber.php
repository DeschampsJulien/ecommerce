<?php
// Ce subscriber rend le compteur du panier disponible dans tous les templates Twig

namespace App\EventSubscriber;

use App\Service\CartService; // Service pour gérer le panier
use Symfony\Component\EventDispatcher\EventSubscriberInterface; // Pour créer un subscriber d'événements
use Symfony\Component\HttpKernel\Event\ControllerEvent;         // Événement déclenché avant l'exécution d'un controller
use Symfony\Component\HttpKernel\KernelEvents;                  // Liste des événements du kernel
use Twig\Environment;                                           // Pour manipuler Twig et ajouter des variables globales

class CartSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cartService;

    // Injection de Twig et du service de panier
    public function __construct(Environment $twig, CartService $cartService)
    {
        $this->twig = $twig;
        $this->cartService = $cartService;
    }

    // Cette méthode est exécutée avant chaque controller
    public function onKernelController(ControllerEvent $event): void
    {
        // Ajoute une variable globale 'cartItemCount' accessible dans tous les templates
        $this->twig->addGlobal('cartItemCount', $this->cartService->getItemCount());
    }

    // Définition des événements auxquels le subscriber s'abonne
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}