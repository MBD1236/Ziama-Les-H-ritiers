<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeFacture = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Commande $commande = null;

    #[ORM\Column]
    private ?int $montantRegle = null;

    #[ORM\Column]
    private ?int $montantRestant = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    /**
     * @var Collection<int, ReglementFacture>
     */
    #[ORM\OneToMany(targetEntity: ReglementFacture::class, mappedBy: 'facture')]
    private Collection $reglementFactures;

    public function __construct()
    {
        $this->reglementFactures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeFacture(): ?string
    {
        return $this->codeFacture;
    }

    public function setCodeFacture(string $codeFacture): static
    {
        $this->codeFacture = $codeFacture;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getMontantRegle(): ?int
    {
        return $this->montantRegle;
    }

    public function setMontantRegle(int $montantRegle): static
    {
        $this->montantRegle = $montantRegle;

        return $this;
    }

    public function getMontantRestant(): ?int
    {
        return $this->montantRestant;
    }

    public function setMontantRestant(int $montantRestant): static
    {
        $this->montantRestant = $montantRestant;

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

    /**
     * @return Collection<int, ReglementFacture>
     */
    public function getReglementFactures(): Collection
    {
        return $this->reglementFactures;
    }

    public function addReglementFacture(ReglementFacture $reglementFacture): static
    {
        if (!$this->reglementFactures->contains($reglementFacture)) {
            $this->reglementFactures->add($reglementFacture);
            $reglementFacture->setFacture($this);
        }

        return $this;
    }

    public function removeReglementFacture(ReglementFacture $reglementFacture): static
    {
        if ($this->reglementFactures->removeElement($reglementFacture)) {
            // set the owning side to null (unless already changed)
            if ($reglementFacture->getFacture() === $this) {
                $reglementFacture->setFacture(null);
            }
        }

        return $this;
    }
}
