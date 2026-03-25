<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Contrôleur de base Symfony
use Symfony\Component\HttpFoundation\Response; // Pour retourner des réponses HTTP
use Symfony\Component\Routing\Attribute\Route; // Pour définir des routes avec attributs
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; // Aide pour gérer login/logout

class SecurityController extends AbstractController
{
    // Route pour la page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère la dernière erreur de connexion, si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();
       
        // Récupère le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rend le template de connexion Twig et passe le dernier username et l'erreur éventuelle
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    // Route pour la déconnexion
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode n'a pas besoin d'être exécutée :
        // Symfony intercepte la route de déconnexion via le firewall et gère la déconnexion automatiquement
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}