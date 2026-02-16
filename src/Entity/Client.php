<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $typeClient = null;

    /**
     * @var Collection<int, Vente>
     */
    #[ORM\OneToMany(targetEntity: Vente::class, mappedBy: 'client')]
    private Collection $ventes;

    /**
     * @var Collection<int, Cadeau>
     */
    #[ORM\OneToMany(targetEntity: Cadeau::class, mappedBy: 'client')]
    private Collection $cadeaux;

    public function __construct()
    {
        $this->ventes = new ArrayCollection();
        $this->cadeaux = new ArrayCollection();
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

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTypeClient(): ?string
    {
        return $this->typeClient;
    }

    public function setTypeClient(string $typeClient): static
    {
        $this->typeClient = $typeClient;

        return $this;
    }

    /**
     * @return Collection<int, Vente>
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): static
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes->add($vente);
            $vente->setClient($this);
        }

        return $this;
    }

    public function removeVente(Vente $vente): static
    {
        if ($this->ventes->removeElement($vente)) {
            if ($vente->getClient() === $this) {
                $vente->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cadeau>
     */
    public function getCadeaux(): Collection
    {
        return $this->cadeaux;
    }

    public function addCadeaux(Cadeau $cadeaux): static
    {
        if (!$this->cadeaux->contains($cadeaux)) {
            $this->cadeaux->add($cadeaux);
            $cadeaux->setClient($this);
        }

        return $this;
    }

    public function removeCadeaux(Cadeau $cadeaux): static
    {
        if ($this->cadeaux->removeElement($cadeaux)) {
            if ($cadeaux->getClient() === $this) {
                $cadeaux->setClient(null);
            }
        }

        return $this;
    }
}
