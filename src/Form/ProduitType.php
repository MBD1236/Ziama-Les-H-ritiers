<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Riz local'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du produit ne peut pas être vide'])
                ]
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'label' => 'Catégorie',
                'required' => true,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie'
            ])
            ->add('prixAchat', IntegerType::class, [
                'label' => 'Prix d\'achat (GNF)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Le prix d\'achat doit être positif'])
                ]
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
            ->add('quantiteStock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'La quantité doit être positive'])
                ]
            ])
            ->add('nombreParCasier', IntegerType::class, [
                'label' => 'Nombre produit par casier',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '1'
                ],
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Le nombre du produit par casier doit être positive'])
                ]
            ])
            ->add('seuilAlerte', IntegerType::class, [
                'label' => 'Seuil d\'alerte',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0'
                ],
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Le seuil doit être positif'])
                ],
                'help' => 'Alerte si stock < seuil'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le produit',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
