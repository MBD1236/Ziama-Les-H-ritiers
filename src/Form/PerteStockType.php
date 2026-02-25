<?php
// src/Form/PerteStockType.php

namespace App\Form;

use App\Entity\PerteStock;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerteStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', ProduitAutocompleteField::class, [
                'label' => 'Produit',
                'required' => true
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité perdue',
                'attr'  => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('motif', ChoiceType::class, [
                'label'   => 'Motif',
                'choices' => [
                    'Périmé'  => 'Périmé',
                    'Avarié'  => 'Avarié',
                    'Cassé'   => 'Cassé',
                    'Autre'   => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description (optionnel)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PerteStock::class]);
    }
}