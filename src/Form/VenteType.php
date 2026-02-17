<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Vente;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', ClientAutocompleteField::class, [
                'label' => 'Client',
                'required' => true
            ])
            ->add('modePaiement', ChoiceType::class, [
                'label' => 'Mode de paiement',
                'required' => true,
                'placeholder' => 'Sélectionner le mode de paiement',
                'choices' => [
                    'Espèces' => 'Espèces',
                    'Crédit' => 'Crédit',
                    'Chèque' => 'Chèque',
                    'Virement' => 'Virement',
                ],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Employé',
                'required' => true,
                'choice_label' => 'username',
                'placeholder' => 'Choisir un employé'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
        ]);
    }
}

