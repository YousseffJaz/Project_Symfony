<?php

namespace App\Repository;

use App\Entity\Folder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Folder::class);
  }


  public function findTypeNotEqualToOne(){
    $query = $this->createQueryBuilder('f');

    $query->andWhere('f.type != :type')
    ->setParameter('type', 1)
    ->addOrderBy('f.name', 'ASC');

    return $query->getQuery()
    ->getResult();
  }
}

