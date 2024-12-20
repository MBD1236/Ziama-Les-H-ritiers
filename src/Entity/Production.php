<?php

namespace App\Entity;

use App\Repository\ProductionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionRepository::class)]
class Production
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productions')]
    private ?Bobine $bobine = null;

    #[ORM\Column]
    private ?int $quantiteUtilisee = null;

    #[ORM\Column]
    private ?int $nombrePack = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateProduction = null;

    /**
     * @var Collection<int, ProductionEmploye>
     */
    #[ORM\OneToMany(targetEntity: ProductionEmploye::class, mappedBy: 'production')]
    private Collection $productionEmployes;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'production')]
    private Collection $commandes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeProduction = null;

    /**
     * @var Collection<int, Cadeau>
     */
    #[ORM\OneToMany(targetEntity: Cadeau::class, mappedBy: 'production')]
    private Collection $cadeaux;

    public function __construct()
    {
        $this->productionEmployes = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->cadeaux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBobine(): ?Bobine
    {
        return $this->bobine;
    }

    public function setBobine(?Bobine $bobine): static
    {
        $this->bobine = $bobine;

        return $this;
    }

    public function getQuantiteUtilisee(): ?int
    {
        return $this->quantiteUtilisee;
    }

    public function setQuantiteUtilisee(int $quantiteUtilisee): static
    {
        $this->quantiteUtilisee = $quantiteUtilisee;

        return $this;
    }

    public function getNombrePack(): ?int
    {
        return $this->nombrePack;
    }

    public function setNombrePack(int $nombrePack): static
    {
        $this->nombrePack = $nombrePack;

        return $this;
    }

    public function getDateProduction(): ?\DateTimeInterface
    {
        return $this->dateProduction;
    }

    public function setDateProduction(\DateTimeInterface $dateProduction): static
    {
        $this->dateProduction = $dateProduction;

        return $this;
    }

    /**
     * @return Collection<int, ProductionEmploye>
     */
    public function getProductionEmployes(): Collection
    {
        return $this->productionEmployes;
    }

    public function addProductionEmploye(ProductionEmploye $productionEmploye): static
    {
        if (!$this->productionEmployes->contains($productionEmploye)) {
            $this->productionEmployes->add($productionEmploye);
            $productionEmploye->setProduction($this);
        }

        return $this;
    }

    public function removeProductionEmploye(ProductionEmploye $productionEmploye): static
    {
        if ($this->productionEmployes->removeElement($productionEmploye)) {
            // set the owning side to null (unless already changed)
            if ($productionEmploye->getProduction() === $this) {
                $productionEmploye->setProduction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setProduction($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getProduction() === $this) {
                $commande->setProduction(null);
            }
        }

        return $this;
    }

    public function getCodeProduction(): ?string
    {
        return $this->codeProduction;
    }

    public function setCodeProduction(?string $codeProduction): static
    {
        $this->codeProduction = $codeProduction;

        return $this;
    }

    /**
     * @return Collection<int, Cadeau>
     */
    public function getCadeaux(): Collection
    {
        return $this->cadeaux;
    }

    public function addCadeau(Cadeau $cadeau): static
    {
        if (!$this->cadeaux->contains($cadeau)) {
            $this->cadeaux->add($cadeau);
            $cadeau->setProduction($this);
        }

        return $this;
    }

    public function removeCadeau(Cadeau $cadeau): static
    {
        if ($this->cadeaux->removeElement($cadeau)) {
            // set the owning side to null (unless already changed)
            if ($cadeau->getProduction() === $this) {
                $cadeau->setProduction(null);
            }
        }

        return $this;
    }
}
