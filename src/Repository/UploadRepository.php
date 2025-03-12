<?php

namespace App\Repository;

use App\Entity\Upload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Upload|null find($id, $lockMode = null, $lockVersion = null)
 * @method Upload|null findOneBy(array $criteria, array $orderBy = null)
 * @method Upload[]    findAll()
 * @method Upload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Upload::class);
  }

  public function search($search){
    $query = $this->createQueryBuilder('u')
    ->andWhere('u.name LIKE :search')
    ->setParameter('search', '%'.$search.'%')
    ->orderBy('u.name', 'ASC');

    return $query->getQuery()->getResult();
  }
}
