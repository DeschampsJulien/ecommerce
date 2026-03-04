<?php

namespace App\EventSubscriber;

use App\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cartService;

    public function __construct(Environment $twig, CartService $cartService)
    {
        $this->twig = $twig;
        $this->cartService = $cartService;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $this->twig->addGlobal('cartItemCount', $this->cartService->getItemCount());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}