<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrderHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderHistory[]    findAll()
 * @method OrderHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderHistory::class);
    }

    public function filter($keyword)
    {
        $query = $this->createQueryBuilder('h')
        ->andWhere('h.title LIKE :keyword')
        ->addOrderBy('h.createdAt', 'DESC')
        ->setParameter('keyword', '%'.$keyword.'%');

        return $query->getQuery()->getResult();
    }
}
