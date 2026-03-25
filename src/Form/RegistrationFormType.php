<?php

namespace App\Form;

use App\Entity\User; // Entité liée au formulaire
use Symfony\Component\Form\AbstractType; // Classe de base pour créer un formulaire
use Symfony\Component\Form\Extension\Core\Type\EmailType; // Champ email
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; // Checkbox (ex : accepter les conditions)
use Symfony\Component\Form\Extension\Core\Type\PasswordType; // Champ mot de passe
use Symfony\Component\Form\Extension\Core\Type\TextType; // Champ texte (ex : nom, adresse)
use Symfony\Component\Form\FormBuilderInterface; // Pour construire le formulaire
use Symfony\Component\OptionsResolver\OptionsResolver; // Pour configurer les options du formulaire
use Symfony\Component\Validator\Constraints\IsTrue; // Validator : doit être true
use Symfony\Component\Validator\Constraints\Length; // Validator : longueur minimale/maximale
use Symfony\Component\Validator\Constraints\NotBlank; // Validator : champ obligatoire

class RegistrationFormType extends AbstractType
{
    // Construction du formulaire
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [ // Nom de l'utilisateur
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank([ // Champ obligatoire
                        'message' => 'Veuillez saisir votre nom',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [ // Email de l'utilisateur
                'label' => 'Adresse email',
            ])
            ->add('plainPassword', PasswordType::class, [ // Mot de passe en clair
                'mapped' => false, // Non mappé directement à l'entité User
                'attr' => ['autocomplete' => 'new-password'], // Attribut HTML
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password', // Champ obligatoire
                    ),
                    new Length(
                        min: 6, // Minimum 6 caractères
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        max: 4096, // Limite de sécurité Symfony
                    ),
                ],
            ])
            ->add('deliveryAddress', TextType::class, [ // Adresse de livraison optionnelle
                'label' => 'Adresse de livraison',
                'required' => false
            ])
            ->add('agreeTerms', CheckboxType::class, [ // Acceptation des conditions
                'mapped' => false, // Non mappé à l'entité
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.', // Doit être coché
                    ),
                ],
            ])
        ;
    }

    // Configuration des options
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // Lie le formulaire à l'entité User
        ]);
    }
}