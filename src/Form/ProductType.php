<?php

namespace App\Form;

use App\Entity\Product; // L'entité liée au formulaire
use Symfony\Component\Form\AbstractType; // Classe de base pour créer un formulaire
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; // Pour le champ "featured"
use Symfony\Component\Form\Extension\Core\Type\MoneyType;    // Pour le champ "price"
use Symfony\Component\Form\FormBuilderInterface; // Pour construire le formulaire
use Symfony\Component\OptionsResolver\OptionsResolver; // Pour configurer les options du formulaire

class ProductType extends AbstractType
{
    // Construction du formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name') // Champ texte pour le nom du produit
            ->add('price', MoneyType::class, [ // Champ pour le prix
                'currency' => 'EUR' // Devise affichée dans le formulaire
            ])
            ->add('image') // Champ texte pour le nom du fichier image
            ->add('featured', CheckboxType::class, [ // Checkbox pour le produit en vedette
                'required' => false // Non obligatoire
            ]);
    }

    // Configuration des options du formulaire
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class, // Lie le formulaire à l'entité Product
        ]);
    }
}