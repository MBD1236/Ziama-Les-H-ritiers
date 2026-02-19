<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeVente = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateVente = null;

    /**
     * @var Collection<int, LigneVente>
     */
    #[ORM\OneToMany(targetEntity: LigneVente::class, mappedBy: 'vente', cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignes;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'vente')]
    private Collection $factures;

    /**
     * @var Collection<int, Livraison>
     */
    #[ORM\OneToMany(targetEntity: Livraison::class, mappedBy: 'vente')]
    private Collection $livraisons;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->livraisons = new ArrayCollection();
        $this->dateVente = new \DateTime();
        $this->statut = 'En cours';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeVente(): ?string
    {
        return $this->codeVente;
    }

    public function setCodeVente(string $codeVente): static
    {
        $this->codeVente = $codeVente;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }


    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateVente(): ?\DateTimeInterface
    {
        return $this->dateVente;
    }

    public function setDateVente(\DateTimeInterface $dateVente): static
    {
        $this->dateVente = $dateVente;
        return $this;
    }

    /**
     * @return Collection<int, LigneVente>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneVente $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setVente($this);
        }
        return $this;
    }

    public function removeLigne(LigneVente $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getVente() === $this) {
                $ligne->setVente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setVente($this);
        }
        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            if ($facture->getVente() === $this) {
                $facture->setVente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function addLivraison(Livraison $livraison): static
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons->add($livraison);
            $livraison->setVente($this);
        }
        return $this;
    }

    public function removeLivraison(Livraison $livraison): static
    {
        if ($this->livraisons->removeElement($livraison)) {
            if ($livraison->getVente() === $this) {
                $livraison->setVente(null);
            }
        }
        return $this;
    }

    /**
     * Calcule le montant total de la vente depuis les lignes
     */
    public function getMontantTotal(): float
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getTotalLigne() ?? 0;
        }
        return $total;
    }
}