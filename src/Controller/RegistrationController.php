<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\SecurityControllerAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;

use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // ENVOI DU MAIL
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@tonsite.fr', 'Ecommerce'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre email')
                    ->htmlTemplate('registration/confirmation_email_email.html.twig') // template mail
            );

            // PAGE INTERMÉDIAIRE – navigateur
            return $this->render('registration/confirmation_email.html.twig', [
                'user' => $user
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // #[Route('/verify/email', name: 'app_verify_email')]
    // public function verifyUserEmail(
    //     Request $request,
    //     EntityManagerInterface $entityManager,
    //     Security $security
    // ): Response {
    //     $id = $request->query->get('id');

    //     if (!$id) {
    //         return $this->redirectToRoute('app_register');
    //     }

    //     $user = $entityManager->getRepository(User::class)->find($id);
    //     if (!$user) {
    //         return $this->redirectToRoute('app_register');
    //     }

    //     if ($user->isVerified()) {
    //         $this->addFlash('info', 'Votre email est déjà vérifié.');
    //         return $this->redirectToRoute('app_home');
    //     }

    //     try {
    //         $this->emailVerifier->handleEmailConfirmation($request, $user);
    //     } catch (VerifyEmailExceptionInterface $exception) {
    //         $this->addFlash('verify_email_error', $exception->getReason());
    //         return $this->redirectToRoute('app_register');
    //     }

    //     // CONNEXION AUTOMATIQUE APRÈS VALIDATION
    //     $security->login($user, SecurityControllerAuthenticator::class, 'main');

    //     $this->addFlash('success', 'Votre email est confirmé. Bienvenue !');

    //     return $this->redirectToRoute('app_home');
    // }

   #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {

        $id = $request->query->get('id');

        if (!$id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Email vérifié !');

        // ✅ CONNEXION AUTOMATIQUE QUI MARCHE À 100%
        $security->login($user, SecurityControllerAuthenticator::class);

        return $this->redirectToRoute('app_home');
    }
}