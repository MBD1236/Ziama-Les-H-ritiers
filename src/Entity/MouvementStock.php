<?php

namespace App\Entity;

use App\Repository\MouvementStockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
class MouvementStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementStocks')]
    private ?Produit $produit = null;


    #[ORM\Column(length: 255)]
    private ?string $typeMouvement = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }


    public function getTypeMouvement(): ?string
    {
        return $this->typeMouvement;
    }

    public function setTypeMouvement(string $typeMouvement): static
    {
        $this->typeMouvement = $typeMouvement;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
