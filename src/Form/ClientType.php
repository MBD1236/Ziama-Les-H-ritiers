<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => true
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('typeClient', ChoiceType::class, [
                'label' => 'Type du client',
                'required' => true,
                'choices' => [
                    'Sélectionner le type du client' => null,
                    'Particulier' => 'Particulier',
                    'Revendeur' => 'Revendeur',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
