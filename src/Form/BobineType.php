<?php

namespace App\Form;

use App\Entity\Bobine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BobineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de bobine',
                'required' => true,
                'choices' => [
                    'Sélectionner le type de bobine' => null,
                    '10Kg' => '10Kg',
                    '15Kg' => '15Kg',
                    '18Kg' => '18Kg',
                    '20Kg' => '20Kg',
                    '25Kg' => '25Kg',
                    '30Kg' => '30Kg',
                    '35Kg' => '35Kg',
                ],
            ])
            ->add('quantiteStock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'required' => true,
            ])
            ->add('prixUnitaire', IntegerType::class, [
                'label' => 'Prix unitaire',
                'required' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bobine::class,
        ]);
    }
}
