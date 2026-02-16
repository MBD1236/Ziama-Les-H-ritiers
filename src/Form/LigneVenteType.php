<?php

namespace App\Form;

use App\Entity\LigneVente;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'label' => 'Produit',
                'required' => true,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un produit'
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'step' => 1
                ]
            ])
            ->add('prixUnitaire', IntegerType::class, [
                'label' => 'Prix unitaire (GNF)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 1
                ]
            ])
            ->add('totalLigne', IntegerType::class, [
                'label' => 'Total ligne (GNF)',
                'required' => true,
                'attr' => [
                    'readonly' => true,
                    'min' => 0
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneVente::class,
        ]);
    }
}
