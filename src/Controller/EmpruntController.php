<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\Repository\UtilisateurRepository;
use App\Form\EmpruntFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/emprunts')]
class EmpruntController extends AbstractController
{
    private $entityManager;
    private $empruntRepository;
    private $livreRepository;
    private $utilisateurRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EmpruntRepository $empruntRepository,
        LivreRepository $livreRepository,
        UtilisateurRepository $utilisateurRepository
    ) {
        $this->entityManager = $entityManager;
        $this->empruntRepository = $empruntRepository;
        $this->livreRepository = $livreRepository;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    // 📌 1️⃣ Listar todos os empréstimos
    #[Route('/', name: 'emprunts_index', methods: ['GET'])]
    public function index(): Response
    {
        $emprunts = $this->empruntRepository->findAll();
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $emprunts,
        ]);
    }

    // 📌 2️⃣ Afficher les détails d'un emprunt
    #[Route('/{id}', name: 'emprunt_show', methods: ['GET'])]
    public function show(Emprunt $emprunt): Response
    {
        return $this->render('emprunt/show.html.twig', [
            'emprunt' => $emprunt,
        ]);
    }

    // 📌 3️⃣ Créer un nouvel emprunt
    #[Route('/new', name: 'emprunt_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $emprunt = new Emprunt();
        $form = $this->createForm(EmpruntFormType::class, $emprunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emprunt->setDateEmprunt(new \DateTime());
            $emprunt->setDateRetourPrevue((new \DateTime())->modify('+14 days')); // 2 semaines par défaut

            $this->entityManager->persist($emprunt);
            $this->entityManager->flush();

            $this->addFlash('success', 'Emprunt créé avec succès!');
            return $this->redirectToRoute('emprunts_index');
        }

        return $this->render('emprunt/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 📌 5️⃣ Supprimer un emprunt (si nécessaire)
    #[Route('/{id}/delete', name: 'emprunt_delete', methods: ['POST'])]
    public function delete(Emprunt $emprunt, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $emprunt->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($emprunt);
            $this->entityManager->flush();
            $this->addFlash('success', 'Emprunt supprimé avec succès!');
        }

        return $this->redirectToRoute('emprunts_index');
    }

    // 📌 4️⃣ Enregistrer le retour d'un livre
    #[Route('/{id}/retour', name: 'emprunt_retour', methods: ['GET', 'POST'])]
    public function retour(Emprunt $emprunt, Request $request): Response
    {
        // Vérifier que l'utilisateur actuel est le propriétaire de l'emprunt ou un admin
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        if ($emprunt->getUtilisateur() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas retourner un livre qui ne vous appartient pas.');
        }

        // Vérifier le token CSRF pour les requêtes POST
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('retour' . $emprunt->getId(), $request->request->get('_token'))) {
            if (!$emprunt->getDateRetourReelle()) {
                $emprunt->setDateRetourReelle(new \DateTime());
                $this->entityManager->flush();
                $this->addFlash('success', 'Livre retourné avec succès !');
            }
        }

        // Rediriger vers la liste des emprunts appropriée
        if ($this->isGranted('ROLE_ADMIN') && $request->get('admin', false)) {
            return $this->redirectToRoute('emprunts_index');
        } else {
            return $this->redirectToRoute('profile_emprunts');
        }
    }
}
