<?php

namespace App\Entity;

use App\Repository\ReglementFactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReglementFactureRepository::class)]
class ReglementFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reglementFactures')]
    private ?Facture $facture = null;

    #[ORM\Column(length: 255)]
    private ?string $modeReglement = null;

    #[ORM\Column]
    private ?int $montantRegle = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getModeReglement(): ?string
    {
        return $this->modeReglement;
    }

    public function setModeReglement(string $modeReglement): static
    {
        $this->modeReglement = $modeReglement;

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
