<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $produitFourni = null;

    /**
     * @var Collection<int, TransactionFournisseur>
     */
    #[ORM\OneToMany(targetEntity: TransactionFournisseur::class, mappedBy: 'fournisseur')]
    private Collection $transactionFournisseurs;

    public function __construct()
    {
        $this->transactionFournisseurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getProduitFourni(): ?string
    {
        return $this->produitFourni;
    }

    public function setProduitFourni(string $produitFourni): static
    {
        $this->produitFourni = $produitFourni;

        return $this;
    }

    /**
     * @return Collection<int, TransactionFournisseur>
     */
    public function getTransactionFournisseurs(): Collection
    {
        return $this->transactionFournisseurs;
    }

    public function addTransactionFournisseur(TransactionFournisseur $transactionFournisseur): static
    {
        if (!$this->transactionFournisseurs->contains($transactionFournisseur)) {
            $this->transactionFournisseurs->add($transactionFournisseur);
            $transactionFournisseur->setFournisseur($this);
        }

        return $this;
    }

    public function removeTransactionFournisseur(TransactionFournisseur $transactionFournisseur): static
    {
        if ($this->transactionFournisseurs->removeElement($transactionFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($transactionFournisseur->getFournisseur() === $this) {
                $transactionFournisseur->setFournisseur(null);
            }
        }

        return $this;
    }
}
