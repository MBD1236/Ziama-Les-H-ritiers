<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Livraison;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'choice_label' => 'codeCommande',
                'label' => 'Commande',
                'required' => true,
                'autocomplete' => true
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user){
                    return $user->getUsername() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Employé',
                'required' => true,
                'autocomplete' => true
            ])
            ->add('dateLivraison', null, [
                'widget' => 'single_text',
                'label' => 'Date de livraison',
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
            'data_class' => Livraison::class,
        ]);
    }
}
