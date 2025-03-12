<?php

namespace App\Repository;

use App\Entity\Flux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Flux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flux[]    findAll()
 * @method Flux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FluxRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Flux::class);
  }


  public function totalAmount($type){
    $query = $this->createQueryBuilder('f')
    ->select('SUM(f.amount) as amount')
    ->andWhere('f.type = :type')
    ->setParameter('type', $type);

    return $query->getQuery()
    ->getResult();
  }
  

  public function findByMonth($type){
    $now = new \DateTime('now', timezone_open('Europe/Paris'));
    $now = $now->setTime(00, 00, 00);

    $query = $this->createQueryBuilder('f')
    ->select('SUM(f.amount) as amount')
    ->andWhere('f.type = :type')
    ->setParameter('type', $type)
    ->andWhere('f.createdAt >= :start')
    ->setParameter('start', $now->format('Y-m-01'));

    return $query->getQuery()->getResult();
  }


  public function totalAmountStartAndEnd($type, $start, $end){
    $query = $this->createQueryBuilder('f')
    ->select('SUM(f.amount) as amount')
    ->andWhere('f.type = :type')
    ->andWhere('f.createdAt >= :start AND f.createdAt <= :end')
    ->setParameter('type', $type)
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59));

    return $query->getQuery()
    ->getResult();
  }
}

