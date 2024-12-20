<?php

namespace App\Form;

use App\Entity\Depense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
               'label' => 'Type de depense',
                'required' => true,
                'choices' => [
                    'Sélectionner le type de depense' => null,
                    'Entretien' => 'Entretien',
                    'Panne' => 'Panne',
                    'Carburant' => 'Carburant',
                    'Imprevu' => 'Imprevu',
                    'Autres' => 'Autres',
                ],
            ])
            ->add('montant', IntegerType::class, [
                'label' => 'Montant',
                'required' => true
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true
            ])
            ->add('dateDepense', null, [
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
        ]);
    }
}
