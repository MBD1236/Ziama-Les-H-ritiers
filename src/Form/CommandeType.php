<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Production;
use App\Entity\User;
use App\Repository\ProductionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true
            ])
            
            ->add('modePaiement', ChoiceType::class, [
                'label' => 'Mode de paiement',
                'required' => true,
                'choices' => [
                    'Sélectionner le mode de paiement' => null,
                    'Espèces' => 'Espèces',
                    'Crédit' => 'Crédit',
                ],
            ])
            ->add('dateCommande', null, [
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('client', ClientAutocompleteField::class)
            ->add('production', ProductionAutocompleteField::class)
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Choisir l\'utilisateur',
                'required' => true,
                'choice_label' => 'username',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
