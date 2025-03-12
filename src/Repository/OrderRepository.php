<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Order::class);
  }

  public function findByStartAndEnd($start, $end){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23,59,59))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }

  public function findByStartAndEndAndTotalAndPaymentType($start, $end, $total){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->andWhere('o.total = :total')
    ->andWhere('o.paymentType = 0')
    ->setParameter('total', $total)
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23,59,59))
    ->setMaxResults(1);

    return $query->getQuery()->getResult();
  }

  public function findByPaymentTypeAndStartAndEnd($type, $start, $end){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->andWhere('o.paymentType = :type')
    ->setParameter('type', $type)
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23,59,59))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findByPaymentType($type){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.paymentType = :type')
    ->setParameter('type', $type)
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findByPaymentMethodAndStartAndEnd($method, $start, $end){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->andWhere('o.paymentMethod = :method')
    ->setParameter('method', $method)
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23,59,59))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findByPaymentMethod($method){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.paymentMethod = :method')
    ->setParameter('method', $method)
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findByStatus($status){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.status = :status')
    ->setParameter('status', $status)
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }


  public function findByStatusAndStartAndEnd($status, $start, $end){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->andWhere('o.status = :status')
    ->setParameter('status', $status)
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23,59,59))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findTotalByMonth(){
    $now = new \DateTime('now', timezone_open('Europe/Paris'));
    $now = $now->setTime(00, 00, 00);
    
    $query = $this->createQueryBuilder('o')
    ->andWhere('o.createdAt >= :start')
    ->select('SUM(o.total) as total')
    ->setParameter('start', $now->format('Y-m-01'))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findTotalByYear(){
    $now = new \DateTime('now', timezone_open('Europe/Paris'));
    $now = $now->setTime(00, 00, 00);

    $query = $this->createQueryBuilder('o')
    ->andWhere('o.createdAt >= :start')
    ->select('SUM(o.total) as total')
    ->setParameter('start', $now->format('Y-01-01'))
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findByMonth(){
    $now = new \DateTime('now', timezone_open('Europe/Paris'));
    $now = $now->setTime(00, 00, 00);

    $query = $this->createQueryBuilder('o')
    ->andWhere('o.createdAt >= :start')
    ->setParameter('start', $now->format('Y-m-01'));

    return $query->getQuery()->getResult();
  }

  public function findByLivraison(){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.orderStatus = 4')
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }


  public function findByDeliveryAndUser($user){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.orderStatus = 4')
    ->andWhere('o.delivery = :user')
    ->setParameter('user', $user)
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }


  public function findByExpedition(){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.orderStatus = 1 OR o.orderStatus = 0')
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }


  public function findByImpayee(){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.status = 0 OR o.status = 1')
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }


  public function findByNotNote(){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.status = 0 OR o.status = 1')
    ->andWhere('o.note2 is null')
    ->addOrderBy('o.createdAt', 'DESC');

    return $query->getQuery()
    ->getResult();
  }

  public function groupByCustomers(){
    $query = $this->createQueryBuilder('o')
    ->select('o.firstname as firstname, COUNT(o.id) as number')
    ->groupBy('o.firstname')
    ->orderBy('o.firstname', 'ASC');

    return $query->getQuery()->getResult();
  }
  
  public function search($search){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->andWhere('o.firstname LIKE :search OR o.lastname LIKE :search OR o.note LIKE :search OR o.id LIKE :search OR l.title LIKE :search')
    ->setParameter('search', '%'.$search.'%')
    ->orderBy('o.createdAt', 'DESC');

    return $query->getQuery()->getResult();
  }
  
  public function searchCustomer($keyword){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->groupBy("o.firstname")
    ->select('o.firstname as firtname')
    ->andWhere('o.firstname LIKE :keyword')
          // ->andWhere('o.firstname LIKE :keyword OR o.lastname LIKE :keyword')
    ->setMaxResults(10)
    ->setParameter('keyword', '%'.$keyword.'%');

    return $query->getQuery()->getResult();
  }


  public function totalAmount(){
    $query = $this->createQueryBuilder('o')
    ->select('SUM(o.total) as total');

    return $query->getQuery()
    ->getResult();
  }


  public function totalAmountByStartAndEnd($start, $end){
    $query = $this->createQueryBuilder('o')
    ->select('SUM(o.total) as total')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59));

    return $query->getQuery()
    ->getResult();
  }


  public function totalAmountByDay($day){
    $query = $this->createQueryBuilder('o')
    ->select('SUM(o.total) as total')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$day, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$day, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59));

    return $query->getQuery()
    ->getResult();
  }

  public function findBestProducts(){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->leftjoin('v.product', 'p')
    ->groupBy("v.product")
      // ->andWhere('p.archive = false')
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, p.title as title')
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }

  public function findBestProductsStartAndEnd($start, $end){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->leftjoin('v.product', 'p')
    ->groupBy("v.product")
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, p.title as title')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
      // ->andWhere('p.archive = false')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59))
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }

  public function findBestCategories(){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->leftjoin('v.product', 'p')
    ->leftjoin('p.category', 'c')
    ->groupBy("p.category")
      // ->andWhere('p.archive = false')
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, c.name as title')
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }

  public function findBestCategoriesStartAndEnd($start, $end){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->leftjoin('v.product', 'p')
    ->leftjoin('p.category', 'c')
    ->groupBy("p.category")
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, c.name as title')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
      // ->andWhere('p.archive = false')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59))
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }

  public function findBestCustomers(){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->groupBy("o.firstname")
      // ->andWhere('p.archive = false')
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, o.firstname as name')
    ->orderBy("SUM(l.price)", "DESC")
    ->setMaxResults(100);

    return $query->getQuery()
    ->getResult();
  }

  public function findBestCustomersStartAndEnd($start, $end){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.variant', 'v')
    ->leftjoin('v.product', 'p')
    ->leftjoin('p.category', 'c')
    ->groupBy("o.firstname")
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, o.firstname as name')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
      // ->andWhere('p.archive = false')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59))
    ->orderBy("SUM(l.price)", "DESC")
    ->setMaxResults(100);

    return $query->getQuery()
    ->getResult();
  }

  public function findByCategoryAndPriceListAndStartAndEnd($category, $start, $end){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.product', 'p')
    ->leftjoin('p.category', 'c')
    ->groupBy("l.priceList")
    ->andWhere('c.id = :category')
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, l.priceList as name')
    ->andWhere('o.createdAt >= :start AND o.createdAt <= :end')
      // ->andWhere('p.archive = false')
    ->setParameter('start', \DateTime::createFromFormat("Y-m-d",$start, new \DateTimeZone('Europe/Paris'))->setTime(00, 00, 00))
    ->setParameter('end', \DateTime::createFromFormat("Y-m-d",$end, new \DateTimeZone('Europe/Paris'))->setTime(23, 59, 59))
    ->setParameter('category', $category)
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }

  public function findByCategoryAndPriceList($category){
    $query = $this->createQueryBuilder('o')
    ->leftjoin('o.lineItems', 'l')
    ->leftjoin('l.product', 'p')
    ->leftjoin('p.category', 'c')
    ->groupBy("l.priceList")
    ->andWhere('c.id = :category')
    ->select('SUM(l.price) as total, SUM(l.quantity) as quantity, l.priceList as name')
    ->setParameter('category', $category)
    ->orderBy("SUM(l.price)", "DESC");

    return $query->getQuery()
    ->getResult();
  }


  public function findOrderNotComplete(){
    $query = $this->createQueryBuilder('o');

    $query->andWhere('o.orderStatus != 3');

    return $query->getQuery()
    ->getResult();
  }
}

