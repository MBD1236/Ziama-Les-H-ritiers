<?php

namespace App\Form;

use App\Entity\Caisse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaisseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', IntegerType::class, [
                'label' => 'Montant',
                'required' => true
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'required' => true,
                'choices' => [
                    'Sélectionner le type de la transaction' => null,
                    'Encaissement' => 'Encaissement',
                    'Décaissement' => 'Décaissement',
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Caisse::class,
        ]);
    }
}
