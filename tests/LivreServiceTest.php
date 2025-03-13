<?php
namespace App\Service;

use App\Entity\Livre;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;

class LivreService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function ajouterLivre(string $titre, string $description, \DateTimeInterface $date, Categorie $categorie, ?string $image = null): Livre
    {
        $livre = new Livre();
        $livre->setTitre($titre);
        $livre->setDescription($description);
        $livre->setDate($date);
        $livre->setCategorie($categorie);
        $livre->setImage($image);

        $this->entityManager->persist($livre);
        $this->entityManager->flush();

        return $livre;
    }
}

