<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ProductController handles CRUD operations for products.
 */
#[Route('', name: 'product_')]
class ProductController extends AbstractController
{
    private EntityManagerInterface $em;
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->validator = $validator;
    }

    /**
     * Lists all products along with their corresponding category.
     * Supports pagination and search filters.
     */
    #[Route('/products', name: 'get_products', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // Obtener filtros de la solicitud
        $categoryId = $request->query->get('category_id');
        $minPrice = $request->query->get('price_min');
        $maxPrice = $request->query->get('price_max');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        
        // Obtener número de página y límite
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));

        // Usar el repositorio para aplicar los filtros
        $products = $this->productRepository->findByCategoryFilters(
            $categoryId, 
            $minPrice, 
            $maxPrice, 
            $startDate, 
            $endDate
        );

        // Formatear la respuesta
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id'          => $product->getId(),
                'name'        => $product->getName(),
                'description' => $product->getDescription(),
                'price'       => $product->getPrice(),
                'created_at'  => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'category'    => [
                    'id'    => $product->getCategory()->getId(),
                    'title' => $product->getCategory()->getTitle()
                ]
            ];
        }

        return $this->json([
            'products' => $data,
            'page'     => $page,
            'limit'    => $limit,
            'total'    => count($products)
        ]);
    }

    /**
     * Displays the details of a specific product.
     */
    #[Route('/products/{id}', name: 'get_product', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id'          => $product->getId(),
            'name'        => $product->getName(),
            'description' => $product->getDescription(),
            'price'       => $product->getPrice(),
            'created_at'  => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'category'    => [
                'id'    => $product->getCategory()->getId(),
                'title' => $product->getCategory()->getTitle()
            ]
        ]);
    }

    /**
     * Adds a new product linked to an existing category.
     */
    #[Route('/products', name: 'create_product', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['error' => 'Invalid JSON.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            if (empty($data['name']) || empty($data['price']) || empty($data['category_id'])) {
                return $this->json(['error' => 'Name, price, and category_id are required.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $category = $this->categoryRepository->find($data['category_id']);
            if (!$category) {
                return $this->json(['error' => 'Category not found.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description'] ?? null);
            $product->setPrice($data['price']);
            $product->setCategory($category);

            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->em->persist($product);
            $this->em->flush();

            return $this->json(['message' => 'Product created successfully.', 'id' => $product->getId()], JsonResponse::HTTP_CREATED);
        } catch (AccessDeniedException $e) {
            return $this->json(['error' => 'Access Denied', 'message' => 'You do not have permission to access this resource.'], JsonResponse::HTTP_FORBIDDEN);
        }
    }

    /**
     * Updates an existing product, including its category.
     */
    #[Route('/products/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);
            if (!$product) {
                return $this->json(['error' => 'Product not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['error' => 'Invalid JSON.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Update values only if present in the request
            if (isset($data['name'])) {
                $product->setName($data['name']);
            }
            if (array_key_exists('description', $data)) {
                $product->setDescription($data['description']);
            }
            if (isset($data['price'])) {
                $product->setPrice($data['price']);
            }

            // If category needs to be updated
            if (!empty($data['category_id'])) {
                $category = $this->categoryRepository->find($data['category_id']);
                if (!$category) {
                    return $this->json(['error' => 'Category not found.'], JsonResponse::HTTP_BAD_REQUEST);
                }
                $product->setCategory($category);
            }

            // Validate changes before saving
            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->em->flush();

            return $this->json(['message' => 'Product updated successfully.']);

        } catch (AccessDeniedException $e) {
            return $this->json(['error' => 'Access Denied', 'message' => 'You do not have permission to access this resource.'], JsonResponse::HTTP_FORBIDDEN);
        }
    }

    /**
     * Deletes a product.
     */
    #[Route('/products/{id}', name: 'delete_product', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);
            if (!$product) {
                return $this->json(['error' => 'Product not found.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $this->em->remove($product);
            $this->em->flush();

            return $this->json(['message' => 'Product deleted successfully.']);

        } catch (AccessDeniedException $e) {
            return $this->json(['error' => 'Access Denied', 'message' => 'You do not have permission to access this resource.'], JsonResponse::HTTP_FORBIDDEN);
        }
    }


}
