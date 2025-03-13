<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }
    
    /**
     * Vérifie si un livre est actuellement emprunté (par n'importe quel utilisateur)
     */
    public function isLivreEmprunte(Livre $livre): bool
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.livre = :livre')
            ->andWhere('e.dateRetourReelle IS NULL')
            ->setParameter('livre', $livre)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $count > 0;
    }
    
    /**
     * Vérifie si un livre est actuellement emprunté par un utilisateur spécifique
     */
    public function isLivreEmprunterParUtilisateur(Livre $livre, Utilisateur $utilisateur): bool
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.livre = :livre')
            ->andWhere('e.utilisateur = :utilisateur')
            ->andWhere('e.dateRetourReelle IS NULL')
            ->setParameter('livre', $livre)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $count > 0;
    }
    
    /**
     * Trouve tous les emprunts actifs (non retournés) d'un utilisateur
     */
    public function findActiveByUser(Utilisateur $user, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.utilisateur = :user')
            ->andWhere('e.dateRetourReelle IS NULL')
            ->setParameter('user', $user)
            ->orderBy('e.dateRetourPrevue', 'ASC');
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Trouve l'historique des emprunts retournés d'un utilisateur
     */
    public function findHistoryByUser(Utilisateur $user, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.utilisateur = :user')
            ->andWhere('e.dateRetourReelle IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('e.dateRetourReelle', 'DESC');
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
}