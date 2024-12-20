<?php

namespace App\Form;

use App\Entity\Cadeau;
use App\Entity\Client;
use App\Entity\Production;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CadeauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true
            ])
            ->add('production', EntityType::class, [
                'class' => Production::class,
                'choice_label' => 'codeProduction',
                'label' => 'Production',
                'required' => true,
                'autocomplete' => true
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function(Client $client){
                    return $client->getNom() . '(' . $client->getTelephone() . ')';
                },
                'autocomplete' => true,
                'label' => 'Client',
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
            'data_class' => Cadeau::class,
        ]);
    }
}
