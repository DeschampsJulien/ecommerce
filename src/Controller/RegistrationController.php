<?php

namespace App\Controller;

use App\Entity\User; // Entité représentant un utilisateur
use App\Form\RegistrationFormType; // Formulaire d'inscription
use App\Security\EmailVerifier; // Service pour vérifier les emails
use App\Security\SecurityControllerAuthenticator; // Authenticator pour la connexion automatique
use Doctrine\ORM\EntityManagerInterface; // Pour gérer la base de données
use Symfony\Bridge\Twig\Mime\TemplatedEmail; // Pour envoyer des emails avec Twig
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security; // Pour connexion automatique
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address; // Adresse email pour l'expéditeur
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Pour hasher le mot de passe
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface; // Gestion des erreurs de verification email

class RegistrationController extends AbstractController
{
    // Injection du service EmailVerifier via le constructeur
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    // Route pour l'inscription
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User(); // Création d'un nouvel utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user); // Création du formulaire d'inscription
        $form->handleRequest($request); // Gestion de la requête POST

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData(); // Récupération du mot de passe en clair

            // Hash du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );
            $user->setIsVerified(false); // L'utilisateur n'est pas encore vérifié

            $entityManager->persist($user); // Prépare l'utilisateur pour insertion
            $entityManager->flush(); // Enregistre l'utilisateur en base

            // ENVOI DU MAIL DE CONFIRMATION
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email', // Route pour vérification
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@tonsite.fr', 'Ecommerce')) // Expéditeur
                    ->to($user->getEmail()) // Destinataire
                    ->subject('Confirmez votre email') // Sujet du mail
                    ->htmlTemplate('registration/confirmation_email_email.html.twig') // Template du mail
            );

            // Affiche une page intermédiaire pour informer l'utilisateur de vérifier son email
            return $this->render('registration/confirmation_email.html.twig', [
                'user' => $user
            ]);
        }

        // Si formulaire non soumis ou invalide, affiche le formulaire
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // Route pour vérifier l'email après clic sur le lien envoyé
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {

        // Récupère l'id de l'utilisateur depuis la query string
        $id = $request->query->get('id');

        if (!$id) {
            return $this->redirectToRoute('app_register'); // si pas d'id, redirige vers inscription
        }

        // Cherche l'utilisateur correspondant à l'id
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_register'); // si utilisateur non trouvé, redirection
        }

        try {
            // Vérifie la validité du lien de confirmation email
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            // Si erreur, message flash et redirection
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        // Message flash pour succès
        $this->addFlash('success', 'Email vérifié !');

        // ✅ Connexion automatique après vérification de l'email
        $security->login($user, SecurityControllerAuthenticator::class);

        return $this->redirectToRoute('app_login'); // Redirige vers page login ou tableau de bord
    }
}