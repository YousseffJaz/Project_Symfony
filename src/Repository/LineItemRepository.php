<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\StockList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LineItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method LineItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method LineItem[]    findAll()
 * @method LineItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LineItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LineItem::class);
    }

    public function totalAmount()
    {
        $query = $this->createQueryBuilder('l')
        ->select('SUM(l.price) as total');

        return $query->getQuery()
        ->getResult();
    }

    public function totalAmountByStartAndEnd($start, $end)
    {
        $query = $this->createQueryBuilder('l')
        ->leftjoin('l.order', 'o')
        ->select('SUM(l.price) as total')
        ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
        ->setParameter('start', \DateTime::createFromFormat('Y-m-d', $start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
        ->setParameter('end', \DateTime::createFromFormat('Y-m-d', $end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59));

        return $query->getQuery()
        ->getResult();
    }

    public function totalAmountByDay($day)
    {
        $query = $this->createQueryBuilder('l')
        ->leftjoin('l.order', 'o')
        ->select('SUM(l.price) as total')
        ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
        ->setParameter('start', \DateTime::createFromFormat('Y-m-d', $day, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
        ->setParameter('end', \DateTime::createFromFormat('Y-m-d', $day, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59));

        return $query->getQuery()
        ->getResult();
    }

    public function findByStock(StockList $stock): array
    {
        return $this->createQueryBuilder('l')
          ->andWhere('l.stock = :stock')
          ->setParameter('stock', $stock)
          ->getQuery()
          ->getResult();
    }

    public function findByOrder(Order $order)
    {
        return $this->createQueryBuilder('l')
          ->andWhere('l.order = :order')
          ->setParameter('order', $order)
          ->getQuery()
          ->getResult();
    }

    public function findByOrderAndProduct(Order $order, Product $product)
    {
        return $this->createQueryBuilder('l')
          ->andWhere('l.order = :order')
          ->andWhere('l.product = :product')
          ->setParameter('order', $order)
          ->setParameter('product', $product)
          ->getQuery()
          ->getResult();
    }
}
