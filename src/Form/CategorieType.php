<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la catégorie',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Riz, Produits laitiers'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la catégorie ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description optionnelle de la catégorie',
                    'rows' => 4
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer la catégorie',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
