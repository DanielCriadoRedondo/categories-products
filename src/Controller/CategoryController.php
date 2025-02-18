<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * CategoryController handles CRUD operations for categories.
 */
#[Route('/categories', name: 'category_')]
class CategoryController extends AbstractController
{
    private EntityManagerInterface $em;
    private CategoryRepository $categoryRepository;
    private ProductRepository $productRepository;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->validator = $validator;
    }

    /**
     * Returns a list of all categories.
     */
    #[Route('', name: 'get_categories', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();
        $data = [];

        foreach ($categories as $category) {
            $data[] = [
                'id'      => $category->getId(),
                'title'   => $category->getTitle(),
                'details' => $category->getDetails()
            ];
        }

        return $this->json($data);
    }

    /**
     * Displays details of a specific category, including its associated products.
     */
    #[Route('/{id}', name: 'get_category', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $products = [];
        foreach ($category->getProducts() as $product) {
            $products[] = [
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getPrice()
            ];
        }

        $data = [
            'id'       => $category->getId(),
            'title'    => $category->getTitle(),
            'details'  => $category->getDetails(),
            'products' => $products
        ];

        return $this->json($data);
    }

    /**
     * Creates a new category.
     *
     * Requires JWT authentication.
     */
    #[Route('', name: 'create_category', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
     public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['error' => 'Invalid JSON.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $category = new Category();
            $category->setTitle($data['title'] ?? '');
            $category->setDetails($data['details'] ?? null);

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                return $this->json(['errors' => array_map(fn ($e) => $e->getMessage(), (array) $errors)], JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->em->persist($category);
            error_log('Persist executed');
            $this->em->flush();
            error_log('Flush executed successfully');


            return $this->json(['message' => 'Category created successfully.', 'id' => $category->getId()], JsonResponse::HTTP_CREATED);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            // Handle duplicate category error
            return $this->json([
                'error' => 'Category already exists',
                'message' => 'A category with this title already exists.'
            ], JsonResponse::HTTP_CONFLICT);
        
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Unexpected Error',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partially updates an existing category.
     *
     * Requires JWT authentication.
     */
    #[Route('/{id}', name: 'update_category', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (isset($data['title'])) {
            $category->setTitle($data['title']);
        }
        if (array_key_exists('details', $data)) {
            $category->setDetails($data['details']);
        }

        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json(['message' => 'Category updated successfully.']);
    }

    
    /**
     * Deletes a category if it has no associated products.
     *
     * Requires JWT authentication.
     */
    #[Route('/{id}', name: 'delete_category', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (count($category->getProducts()) > 0) {
            return $this->json(['error' => 'Cannot delete category with associated products.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->remove($category);
        $this->em->flush();

        return $this->json(['message' => 'Category deleted successfully.']);
    }
}
