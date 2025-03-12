<?php

namespace App\Repository;

use App\Entity\Preorder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Preorder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Preorder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Preorder[]    findAll()
 * @method Preorder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreorderRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Preorder::class);
  }
}
