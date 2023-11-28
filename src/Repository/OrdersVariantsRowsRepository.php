<?php

namespace App\Repository;

use App\Entity\OrdersVariantsRows;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrdersVariantsRows>
 *
 * @method OrdersVariantsRows|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdersVariantsRows|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdersVariantsRows[]    findAll()
 * @method OrdersVariantsRows[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersVariantsRowsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdersVariantsRows::class);
    }

//    /**
//     * @return OrdersVariantsRows[] Returns an array of OrdersVariantsRows objects
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

//    public function findOneBySomeField($value): ?OrdersVariantsRows
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
