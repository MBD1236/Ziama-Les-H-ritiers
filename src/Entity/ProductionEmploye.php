<?php

namespace App\Entity;

use App\Repository\ProductionEmployeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionEmployeRepository::class)]
class ProductionEmploye
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productionEmployes')]
    private ?Production $production = null;

    #[ORM\ManyToOne(inversedBy: 'productionEmployes')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduction(): ?Production
    {
        return $this->production;
    }

    public function setProduction(?Production $production): static
    {
        $this->production = $production;

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
}
