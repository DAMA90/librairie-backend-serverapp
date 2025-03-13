<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }
    
    /**
     * Trouve les livres par catégorie
     */
    public function findByCategorie(int $categorieId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.categorie = :categorieId')
            ->setParameter('categorieId', $categorieId)
            ->orderBy('l.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Recherche de livres par titre ou description
     */
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.titre LIKE :search OR l.description LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('l.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}