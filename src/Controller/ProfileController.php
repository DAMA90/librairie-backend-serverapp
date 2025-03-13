<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfileType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $empruntRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EmpruntRepository $empruntRepository
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->empruntRepository = $empruntRepository;
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     */
    #[Route('/', name: 'profile_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les emprunts actifs de l'utilisateur (limité aux 5 plus récents)
        $empruntsActifs = $this->empruntRepository->findActiveByUser($user, 5);
        
        return $this->render('profile/index.html.twig', [
            'emprunts_actifs' => $empruntsActifs,
        ]);
    }

    /**
     * Affiche tous les emprunts de l'utilisateur
     */
    #[Route('/emprunts', name: 'profile_emprunts', methods: ['GET'])]
    public function emprunts(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer tous les emprunts actifs de l'utilisateur
        $empruntsActifs = $this->empruntRepository->findActiveByUser($user);
        
        // Récupérer l'historique des emprunts (retournés)
        $empruntsHistorique = $this->empruntRepository->findHistoryByUser($user);

        return $this->render('profile/emprunts.html.twig', [
            'emprunts_actifs' => $empruntsActifs,
            'emprunts_historique' => $empruntsHistorique,
        ]);
    }

    /**
     * Éditer le profil utilisateur
     */
    #[Route('/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                // $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                // $user->setMotDepasse($hashedPassword);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès!');

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}