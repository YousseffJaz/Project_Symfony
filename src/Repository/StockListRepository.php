<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Entity\StockList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockList|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockList|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockList[]    findAll()
 * @method StockList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockList::class);
    }

    public function findQuantityByProduct($product)
    {
        $query = $this->createQueryBuilder('s')
        ->select('SUM(s.quantity) as quantity, p.id as id')
        ->groupBy('s.product')
        ->leftJoin('s.product', 'p')
        ->andWhere('p.id = :product')
        ->andWhere("s.name != 'Faux stock'")
        ->setParameter('product', $product);

        return $query->getQuery()->getResult();
    }

    public function findQuantityByProductAndStock($product, $stock)
    {
        $query = $this->createQueryBuilder('s')
        ->select('SUM(s.quantity) as quantity, p.id as id')
        ->groupBy('s.product, p.id')
        ->leftJoin('s.product', 'p')
        ->andWhere('p.id = :product')
        ->andWhere('s.name = :stock')
        ->setParameter('stock', $stock)
        ->setParameter('product', $product);

        return $query->getQuery()->getResult();
    }

    public function findStockName()
    {
        $query = $this->createQueryBuilder('s')
        ->select('s.name as name')
        ->groupBy('s.name')
        ->orderBy('s.name', 'ASC')
        ->andWhere("s.name != 'Faux stock'");

        return $query->getQuery()->getResult();
    }

    public function findAllStockName()
    {
        $query = $this->createQueryBuilder('s')
        ->select('s.name as name')
        ->groupBy('s.name')
        ->orderBy('s.name', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function findStockInfAlert($product, $alert)
    {
        $query = $this->createQueryBuilder('s')
        ->leftJoin('s.product', 'p')
        ->andWhere('s.quantity > 0 AND s.quantity <= :alert')
        ->andWhere('p.id = :product')
        ->setParameter('alert', $alert)
        ->setParameter('product', $product);

        return $query->getQuery()->getResult();
    }

    public function findOneById(int $id): ?StockList
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findByProduct(Product $product): array
    {
        return $this->findBy(['product' => $product]);
    }

    public function incrementStock(StockList $stock, int $quantity): void
    {
        $stock->setQuantity($stock->getQuantity() + $quantity);
        $this->getEntityManager()->flush();
    }

    public function calculateStockValue(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.name, SUM(s.quantity * p.price) as value')
            ->leftJoin('s.product', 'p')
            ->where('p.archive = false')
            ->andWhere('p.digital = false')
            ->groupBy('s.name')
            ->getQuery()
            ->getResult();
    }
}
