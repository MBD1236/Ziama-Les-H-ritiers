<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Vente;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            ]);

        // Champ user visible seulement pour l'admin
        if ($options['show_user']) {
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Employé',
                'required' => true,
                'choice_label' => 'username',
                'placeholder' => 'Choisir un employé'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
            'show_user'  => true, // true par défaut
        ]);
    }
}