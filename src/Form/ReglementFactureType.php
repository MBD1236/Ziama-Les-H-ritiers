<?php

namespace App\Form;

use App\Entity\Facture;
use App\Entity\ReglementFacture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReglementFactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modeReglement', ChoiceType::class, [
                'label' => 'Mode de réglement',
                'required' => true,
                'choices' => [
                    'Sélectionner le mode de réglement' => null,
                    'Espèces' => 'Espèces',
                    'Orange money' => 'Orange money',
                    'Mobile money' => 'Mobile money'
                ],
            ])
            ->add('montantRegle', IntegerType::class, [
                'label' => 'Montant à régler',
                'required' => true
            ])
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReglementFacture::class,
        ]);
    }
}
