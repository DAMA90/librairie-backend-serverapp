<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Emprunt;
use App\Entity\Commentaire;
use App\Form\LivreType;
use App\Form\CommentaireType;
use App\Repository\LivreRepository;
use App\Repository\EmpruntRepository;
use App\Repository\CommentaireRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/livres')]
class LivreController extends AbstractController
{
    private $entityManager;
    private $livreRepository;
    private $empruntRepository;
    private $commentaireRepository;
    private $categorieRepository;
    private $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LivreRepository $livreRepository,
        EmpruntRepository $empruntRepository,
        CommentaireRepository $commentaireRepository,
        CategorieRepository $categorieRepository,
        SluggerInterface $slugger
    ) {
        $this->entityManager = $entityManager;
        $this->livreRepository = $livreRepository;
        $this->empruntRepository = $empruntRepository;
        $this->commentaireRepository = $commentaireRepository;
        $this->categorieRepository = $categorieRepository;
        $this->slugger = $slugger;
    }

    // Liste des livres
    #[Route('/', name: 'livres_index', methods: ['GET'])]
   public function index(Request $request, CategorieRepository $categorieRepository): Response
{
    $categorieId = $request->query->get('categorie');
    $recherche = $request->query->get('recherche');
    
    // Récupérer toutes les catégories
    $categories = $categorieRepository->findAll();
    
    if ($categorieId) {
        $livres = $this->livreRepository->findByCategorie($categorieId);
    } elseif ($recherche) {
        $livres = $this->livreRepository->findBySearch($recherche);
    } else {
        $livres = $this->livreRepository->findAll();
    }
    
    return $this->render('livre/index.html.twig', [
        'livres' => $livres,
        'categories' => $categories,
    ]);
}

    // Détails d'un livre
    #[Route('/{id}', name: 'livre_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(Livre $livre, Request $request): Response
    {
        // Vérifier si le livre est déjà emprunté par l'utilisateur courant
        $dejaEmprunte = false;
        $user = $this->getUser();
        
        if ($user) {
            $dejaEmprunte = $this->empruntRepository->isLivreEmprunterParUtilisateur($livre, $user);
        }
        
        // Vérifier si le livre est disponible (pas emprunté actuellement par quelqu'un d'autre)
        $disponible = !$this->empruntRepository->isLivreEmprunte($livre);
        
        // Récupérer les commentaires existants pour ce livre
        $commentaires = $this->commentaireRepository->findBy(
            ['livre' => $livre],
            ['date' => 'DESC']
        );
        
        // Créer un nouveau commentaire si l'utilisateur est connecté
        $commentForm = null;
        
        if ($user) {
            $commentaire = new Commentaire();
            $commentaire->setLivre($livre);
            $commentaire->setUtilisateur($user);
            $commentaire->setDate(new \DateTime());
            
            $commentForm = $this->createForm(CommentaireType::class, $commentaire);
            $commentForm->handleRequest($request);
            
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $this->entityManager->persist($commentaire);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Votre commentaire a été publié avec succès!');
                
                // Redirection pour éviter la soumission multiple du formulaire
                return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
            }
        }
        
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
            'deja_emprunte' => $dejaEmprunte,
            'disponible' => $disponible,
            'commentaires' => $commentaires,
            'commentForm' => $commentForm ? $commentForm->createView() : null,
        ]);
    }

    // Emprunter un livre
    #[Route('/{id}/emprunter', name: 'livre_emprunter', methods: ['POST'])]
    public function emprunter(Livre $livre, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si le livre est déjà emprunté
        if ($this->empruntRepository->isLivreEmprunte($livre)) {
            $this->addFlash('error', 'Ce livre n\'est plus disponible.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }
        
        // Vérifier si l'utilisateur a déjà emprunté ce livre
        if ($this->empruntRepository->isLivreEmprunterParUtilisateur($livre, $user)) {
            $this->addFlash('error', 'Vous avez déjà emprunté ce livre.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }
        
        // Créer un nouvel emprunt
        $emprunt = new Emprunt();
        $emprunt->setLivre($livre);
        $emprunt->setUtilisateur($user);
        $emprunt->setDateEmprunt(new \DateTime());
        $emprunt->setDateRetourPrevue((new \DateTime())->modify('+14 days'));
        
        $this->entityManager->persist($emprunt);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Livre emprunté avec succès ! À retourner avant le ' . $emprunt->getDateRetourPrevue()->format('d/m/Y'));
        
        return $this->redirectToRoute('profile_emprunts');
    }

    // Partie ADMIN : Créer un nouveau livre
    #[Route('/new', name: 'livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $this->handleImageUpload($livre, $imageFile);
            }
            
            $this->entityManager->persist($livre);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Livre ajouté avec succès !');
            return $this->redirectToRoute('livres_index');
        }

        return $this->render('livre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Partie ADMIN : Modifier un livre
    #[Route('/{id}/edit', name: 'livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                // Si une nouvelle image est téléchargée, supprimez l'ancienne si elle existe
                if ($livre->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $livre->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $this->handleImageUpload($livre, $imageFile);
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Livre modifié avec succès !');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }

        return $this->render('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form->createView(),
        ]);
    }

    // Partie ADMIN : Supprimer un livre
    #[Route('/{id}/delete', name: 'livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->request->get('_token'))) {
            // Vérifier si le livre est actuellement emprunté
            if ($this->empruntRepository->isLivreEmprunte($livre)) {
                $this->addFlash('error', 'Ce livre ne peut pas être supprimé car il est actuellement emprunté.');
                return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
            }
            
            // Supprimer l'image associée si elle existe
            if ($livre->getImage()) {
                $imagePath = $this->getParameter('images_directory') . '/' . $livre->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $this->entityManager->remove($livre);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Livre supprimé avec succès !');
        }

        return $this->redirectToRoute('livres_index');
    }
    
    /**
     * Gère l'upload d'une image pour un livre
     */
    private function handleImageUpload(Livre $livre, $imageFile): void
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
        
        try {
            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
            
            $livre->setImage($newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
        }
    }
}