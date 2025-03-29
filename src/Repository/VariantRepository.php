<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Variant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Variant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Variant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Variant[]    findAll()
 * @method Variant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Variant::class);
    }

    public function filter($keyword, $stockList)
    {
        $query = $this->createQueryBuilder('v')
        ->select('v.id', 'v.title', 'v.price', 's.quantity', 'p.title as product_name')
        ->leftjoin('v.product', 'p')
        ->leftjoin('p.stockLists', 's');

        if ($keyword) {
            $query
            ->andWhere('(LOWER(v.title) LIKE LOWER(:keyword) OR LOWER(p.title) LIKE LOWER(:keyword))')
            ->andWhere('s.name = :stockList')
            ->andWhere('v.archive = false')
            ->setParameter('keyword', '%'.strtolower($keyword).'%')
            ->setParameter('stockList', $stockList);
        }

        $query->addOrderBy('v.title', 'ASC');

        $results = $query->getQuery()->getArrayResult();

        // Format results to match expected structure
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'].' ('.$item['product_name'].')',
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ];
        }, $results);
    }

    public function search($search)
    {
        $query = $this->createQueryBuilder('v')
        ->andWhere('v.title LIKE :search')
        ->setParameter('search', '%'.$search.'%')
        ->setMaxResults(1);

        return $query->getQuery()->getResult();
    }

    public function findOneById(int $id): ?Variant
    {
        return $this->findOneBy(['id' => $id]);
    }
}
