<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findProductAlmostSoldOut()
    {
        return $this->createQueryBuilder('p')
        ->leftJoin('p.stockLists', 's')
        ->andWhere('p.alert >= s.quantity OR s.quantity IS NULL')
        ->andWhere('p.archive = false')
        ->getQuery()
        ->getResult();
    }

    public function findOneById(int $id): ?Product
    {
        return $this->findOneBy(['id' => $id]);
    }
}
