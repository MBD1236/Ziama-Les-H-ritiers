<?php

namespace App\Form;

use App\Entity\Vente;
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
            ->add('vente', EntityType::class, [
                'class' => Vente::class,
                'choice_label' => function(Vente $vente){
                    $first = $vente->getLignes()->first();
                    $prod = $first ? $first->getProduit()->getNom() : '';
                    $qty = $first ? $first->getQuantite() : 0;
                    $montant = 0;
                    foreach ($vente->getLignes() as $l) {
                        $montant += $l->getTotalLigne() ?? ($l->getProduit()->getPrixVente() * $l->getQuantite());
                    }
                    return $vente->getCodeVente() . ' (' . $prod . ' : ' . $qty .' =>' . number_format($montant, 2, ',', ' ') . ' GNF) - ' . $vente->getClient()->getNom();
                },
                'label' => 'Vente',
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
