<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Récupérer tous les commentaires en attente de modération
     * @return Commentaire[]
     */

 public function findPendingComments(): array
{
    return $this->createQueryBuilder('c')
        ->where('c.isApproved = :approved')
        ->setParameter('approved', false)
        ->orderBy('c.date', 'DESC')
        ->getQuery()
        ->getResult();
}


}

