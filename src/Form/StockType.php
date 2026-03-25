<?php

namespace App\Form;

use App\Entity\Stock; // Entité liée au formulaire
use Symfony\Component\Form\AbstractType; // Classe de base pour créer un formulaire
use Symfony\Component\Form\FormBuilderInterface; // Pour construire le formulaire
use Symfony\Component\OptionsResolver\OptionsResolver; // Pour configurer les options du formulaire
use Symfony\Component\Form\Extension\Core\Type\TextType; // Champ texte (ex : taille)
use Symfony\Component\Form\Extension\Core\Type\IntegerType; // Champ entier (ex : quantité)

class StockType extends AbstractType
{
    // Construction du formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Taille du produit (S, M, L, XL…)
            ->add('size', TextType::class, [
                'label' => 'Taille',
                'attr' => [
                    'placeholder' => 'Ex: S, M, L',
                    'class' => 'form-control'
                ],
            ])
            
            // Quantité en stock
            ->add('quantity', IntegerType::class, [
                'label' => 'Stock',
                'attr' => [
                    'placeholder' => 'Ex: 10',
                    'class' => 'form-control',
                    'min' => 0 // Minimum autorisé = 0
                ],
            ]);
    }

    // Configuration des options du formulaire
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stock::class, // Lie le formulaire à l'entité Stock
        ]);
    }
}