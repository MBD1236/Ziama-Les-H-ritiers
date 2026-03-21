<?php

namespace App\Form;

use App\Entity\LigneVente;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class LigneVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', ProduitAutocompleteField::class, [
                'label' => 'Produit',
                'required' => true
            ])
            ->add('prixVente', IntegerType::class, [
                'label' => 'Prix de vente (GNF)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Le prix de vente doit être positif'])
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'step' => 1
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
