<?php

namespace App\Form;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProduitAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Produit::class,
                'label' => 'Choisir le produit',
                'required' => true,
                'choice_label' => function (Produit $produit) {
                    return $produit->getNom() . ' (' . $produit->getQuantiteStock() . ')';
                },
                'query_builder' => function (ProduitRepository $pr) {
                    return $pr->createQueryBuilder('p')
                            ->where('p.nombrePack > 0');
                },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
