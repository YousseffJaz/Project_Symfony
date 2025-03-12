<?php

namespace App\Repository;

use App\Entity\PriceList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceList|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceList|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceList[]    findAll()
 * @method PriceList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, PriceList::class);
  }


  public function findByProduct($product){
    $query = $this->createQueryBuilder('pl')
    ->select('pl.price as price')
    ->leftJoin('pl.variant', 'v')
    ->leftJoin('v.product', 'p')
    ->andWhere('p.id = :product')
    ->setParameter('product', $product)
    ->addOrderBy('pl.price', 'ASC');

    return $query->getQuery()->getResult();
  }


  public function findPriceListName(){
    $query = $this->createQueryBuilder('p')
    ->select('p.title as name')
    ->groupBy("p.title")
    ->orderBy("p.title", "ASC");

    return $query->getQuery()->getResult();
  }
}

