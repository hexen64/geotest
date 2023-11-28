<?php

namespace App\Repository;

use App\Entity\OrdersRows;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrdersRows>
 *
 * @method OrdersRows|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdersRows|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdersRows[]    findAll()
 * @method OrdersRows[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRowsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdersRows::class);
    }

//    /**
//     * @return OrdersRows[] Returns an array of OrdersRows objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrdersRows
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
