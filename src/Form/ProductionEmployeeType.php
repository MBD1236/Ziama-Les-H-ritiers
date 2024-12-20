<?php

namespace App\Form;

use App\Entity\Production;
use App\Entity\ProductionEmploye;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionEmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('production', EntityType::class, [
                'class' => Production::class,
                'choice_label' => 'codeProduction',
                'autocomplete' => true,
                'label' => 'Production',
                'required' => true
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUsername() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Employé',
                'required' => true,
                'autocomplete' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductionEmploye::class,
        ]);
    }
}
