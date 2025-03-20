<?php

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

  public function filter($keyword, $stockList){
    $fields = ['v.id', 'v.title', 'l.price', 's.quantity'];
    $query = $this->createQueryBuilder('v')
    ->leftjoin('v.priceLists', 'l')
    ->leftjoin('v.product', 'p')
    ->leftjoin('p.stockLists', 's');

    if($keyword) {
      $query->select($fields)
      ->andWhere('v.title LIKE :keyword')
      ->andWhere('s.name = :stockList')
      ->andWhere('v.archive = false')
      ->setParameter('keyword', '%'.$keyword.'%')
      ->setParameter('stockList', $stockList);
    }

    $query->addOrderBy('v.title', "ASC");

    return $query->getQuery()
    ->getResult();
  }


  public function search($search){
    $query = $this->createQueryBuilder('v')
    ->andWhere('v.title LIKE :search')
    ->setParameter('search', '%'.$search.'%')
    ->setMaxResults(1);

    return $query->getQuery()->getResult();
  }
}

