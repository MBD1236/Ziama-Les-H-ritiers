<?php

namespace App\Form;

use App\Entity\Production;
use App\Repository\ProductionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductionAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Production::class,
                'label' => 'Choisir la production',
                'required' => true,
                'choice_label' => function (Production $production) {
                    return $production->getCodeProduction() . ' (' . $production->getNombrePack() . ')';
                },
                'query_builder' => function (ProductionRepository $pr) {
                    return $pr->createQueryBuilder('p')
                            ->where('p.nombrePack > 0');
                },

            // choose which fields to use in the search
            // if not passed, *all* fields are used
            // 'searchable_fields' => ['name'],

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
