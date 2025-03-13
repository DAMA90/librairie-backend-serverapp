<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/commentaires')]
class CommentaireController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommentaireRepository $commentaireRepository;

    public function __construct(EntityManagerInterface $entityManager, CommentaireRepository $commentaireRepository)
    {
        $this->entityManager = $entityManager;
        $this->commentaireRepository = $commentaireRepository;
    }

    // 📌 Récupérer les commentaires en attente (Sem restrições temporariamente)
    #[Route('/pending', name: 'get_pending_comments', methods: ['GET'])]
    public function getPendingComments(): JsonResponse
    {
        // 🔥 REMOVIDO: $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $comments = $this->commentaireRepository->findPendingComments();

        return $this->json($comments, Response::HTTP_OK, [], ['groups' => 'comment:read']);
    }

    // 📌 Approuver un commentaire
    #[Route('/{id}/approve', name: 'approve_comment', methods: ['PATCH'])]
    public function approveComment(int $id): JsonResponse
    {
        $comment = $this->commentaireRepository->find($id);

        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $comment->setIsApproved(true);
        $this->entityManager->flush();

        return $this->json(['message' => 'Commentaire approuvé avec succès'], Response::HTTP_OK);
    }

    // 📌 Supprimer un commentaire (Rejet)
    #[Route('/{id}/reject', name: 'reject_comment', methods: ['DELETE'])]
    public function rejectComment(int $id): JsonResponse
    {
        $comment = $this->commentaireRepository->find($id);

        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(['message' => 'Commentaire supprimé'], Response::HTTP_OK);
    }
}
