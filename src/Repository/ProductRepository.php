<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    
    /**
     * Returns paginated results of products.
     *
     * @param int $page  Current page number.
     * @param int $limit Number of items per page.
     * @return Product[]
     */
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('p')
                   ->orderBy('p.createdAt', 'DESC')
                   ->setFirstResult(($page - 1) * $limit)
                   ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * Returns paginated and filtered results of products.
     *
     * Supported filters:
     * - category_id: int
     * - price_min: decimal
     * - price_max: decimal
     * - date_from: string (Y-m-d)
     * - date_to: string (Y-m-d)
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return Product[]
     */
    public function findFilteredPaginated(int $page = 1, int $limit = 10, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($filters['category_id'])) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $filters['category_id']);
        }
        if (isset($filters['price_min'])) {
            $qb->andWhere('p.price >= :price_min')
               ->setParameter('price_min', $filters['price_min']);
        }
        if (isset($filters['price_max'])) {
            $qb->andWhere('p.price <= :price_max')
               ->setParameter('price_max', $filters['price_max']);
        }
        if (isset($filters['date_from'])) {
            $qb->andWhere('p.createdAt >= :date_from')
               ->setParameter('date_from', new \DateTime($filters['date_from']));
        }
        if (isset($filters['date_to'])) {
            $qb->andWhere('p.createdAt <= :date_to')
               ->setParameter('date_to', new \DateTime($filters['date_to']));
        }

        $qb->orderBy('p.createdAt', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns products filtered by category, price range, and date range.
     *
     * @param int|null $categoryId
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Product[]
     */
    public function findByCategoryFilters(?int $categoryId, ?float $minPrice, ?float $maxPrice, ?string $startDate, ?string $endDate): array
    {
        $qb = $this->createQueryBuilder('p');
        if ($categoryId) {
            $qb->andWhere('p.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($minPrice) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        if ($startDate) {
            $qb->andWhere('p.created_at >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $qb->andWhere('p.created_at <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate));
        }

        return $qb->getQuery()->getResult();
    }
}
