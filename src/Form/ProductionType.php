<?php

namespace App\Form;

use App\Entity\Bobine;
use App\Entity\Production;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bobine', EntityType::class, [
                'class' => Bobine::class,
                'choice_label' => function (Bobine $bobine) {
                    return $bobine->getType() . ' ( ' . $bobine->getQuantiteStock() . ' en stock)';
                },
            ])
            ->add('quantiteUtilisee', IntegerType::class, [
                'label' => 'Quantité à utiliser',
                'required' => true
            ])
            ->add('nombrePack', IntegerType::class, [
                'label' => 'Nombre de pack obtenu',
                'required' => true
            ])
            ->add('dateProduction', null, [
                'label' => 'Date de production',
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Production::class,
        ]);
    }
}
