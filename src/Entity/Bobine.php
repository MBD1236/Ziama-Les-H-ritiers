<?php

namespace App\Entity;

use App\Repository\BobineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BobineRepository::class)]
class Bobine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $quantiteStock = null;

    #[ORM\Column]
    private ?int $prixUnitaire = null;

    /**
     * @var Collection<int, MouvementBobine>
     */
    #[ORM\OneToMany(targetEntity: MouvementBobine::class, mappedBy: 'bobine')]
    private Collection $mouvementBobines;

    /**
     * @var Collection<int, Production>
     */
    #[ORM\OneToMany(targetEntity: Production::class, mappedBy: 'bobine')]
    private Collection $productions;

    public function __construct()
    {
        $this->mouvementBobines = new ArrayCollection();
        $this->productions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;

        return $this;
    }

    public function getPrixUnitaire(): ?int
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(int $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    /**
     * @return Collection<int, MouvementBobine>
     */
    public function getMouvementBobines(): Collection
    {
        return $this->mouvementBobines;
    }

    public function addMouvementBobine(MouvementBobine $mouvementBobine): static
    {
        if (!$this->mouvementBobines->contains($mouvementBobine)) {
            $this->mouvementBobines->add($mouvementBobine);
            $mouvementBobine->setBobine($this);
        }

        return $this;
    }

    public function removeMouvementBobine(MouvementBobine $mouvementBobine): static
    {
        if ($this->mouvementBobines->removeElement($mouvementBobine)) {
            // set the owning side to null (unless already changed)
            if ($mouvementBobine->getBobine() === $this) {
                $mouvementBobine->setBobine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Production>
     */
    public function getProductions(): Collection
    {
        return $this->productions;
    }

    public function addProduction(Production $production): static
    {
        if (!$this->productions->contains($production)) {
            $this->productions->add($production);
            $production->setBobine($this);
        }

        return $this;
    }

    public function removeProduction(Production $production): static
    {
        if ($this->productions->removeElement($production)) {
            // set the owning side to null (unless already changed)
            if ($production->getBobine() === $this) {
                $production->setBobine(null);
            }
        }

        return $this;
    }
}
