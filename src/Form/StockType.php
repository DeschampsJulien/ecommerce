<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class StockType extends AbstractType
{
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
                    'min' => 0
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}