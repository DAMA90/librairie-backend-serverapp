<?php

namespace App\Entity;

use App\Repository\commentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: commentaireRepository::class)]
#[ApiResource]
class commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["comment:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["comment:read"])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["comment:read"])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[Groups(["comment:read"])]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Livre::class)]
    #[ORM\JoinColumn(name: 'fk_id_livre', referencedColumnName: 'id', nullable: false)]
    #[Groups(["comment:read"])]
    private ?Livre $livre = null;

    // Nouveau champ pour modération
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(["comment:read"])]
    private ?bool $isApproved = false;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getUtilisateur(): ?utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getLivre(): ?livre
    {
        return $this->livre;
    }

    public function setLivre(?livre $livre): self
    {
        $this->livre = $livre;
        return $this;
    }

    public function getIsApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;
        return $this;
    }
}
