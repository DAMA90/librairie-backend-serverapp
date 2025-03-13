<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/categories')]
class CategorieController extends AbstractController
{
    private $entityManager;
    private $categorieRepository;

    public function __construct(EntityManagerInterface $entityManager, CategorieRepository $categorieRepository)
    {
        $this->entityManager = $entityManager;
        $this->categorieRepository = $categorieRepository;
    }

    // 1️ Lister toutes les catégories (TEMPLATE TWIG)
    #[Route('/', name: 'categories_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categorieRepository->findAll();

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Alias pour maintenir la compatibilité avec les routes existantes
    #[Route('/', name: '"get_categories"', methods: ['GET'])]
    public function getCategories(): Response
    {
        return $this->index();
    }

    // 2️ Afficher une seule catégorie
    #[Route('/{id}', name: 'show_category', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    // 3️ Créer une nouvelle catégorie (FORMULAIRE TWIG)
    #[Route('/new', name: 'create_category', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($categorie);
            $this->entityManager->flush();

            return $this->redirectToRoute('categories_index');
        }

        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }

    // 4️ Éditer une catégorie (FORMULAIRE TWIG)
    #[Route('/{id}/edit', name: 'update_category', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('categories_index');
        }

        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }

    // 5️ Supprimer une catégorie
    #[Route('/{id}/delete', name: 'delete_category', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Categorie $categorie): Response
    {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($categorie);
            $this->entityManager->flush();
        }
        
        return $this->redirectToRoute('categories_index');
    }

    // 6️⃣ API JSON: Lister les catégories
    #[Route('/api/list', name: 'api_get_categories', methods: ['GET'])]
    public function apiGetCategories(): JsonResponse
    {
        $categories = $this->categorieRepository->findAll();
        return $this->json($categories);
    }

    // 7️⃣ API JSON: Créer une catégorie
    #[Route('/api/create', name: 'api_create_category', methods: ['POST'])]
    public function apiCreateCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['description'])) {
            return $this->json(['message' => 'Données invalides'], 400);
        }

        $category = new Categorie();
        $category->setDescription($data['description']);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json(['message' => 'Catégorie créée avec succès'], 201);
    }
}