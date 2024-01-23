<?php

namespace App\Repository;

use App\Entity\OrdersVariants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrdersVariants>
 *
 * @method OrdersVariants|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdersVariants|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdersVariants[]    findAll()
 * @method OrdersVariants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersVariantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdersVariants::class);
    }

//    public function findWithRows(): array
//    {
//        $this->createQueryBuilder('ov')
//            ->leftJoin('App::Variant', 'v', 'WITH', 'ov.variantId = v.id')
//            ->leftJoin('App::VariantsRows', 'vr', 'WITH', 'ov.variantId = vr.variant_id')
//            ->leftJoin('App::OrdersVariantsRows', 'ovr', 'WITH', 'ov.orderId = ovr.orderId',
//                'ov.variantId = ovr.variantId')
//            ->leftJoin('App::Rows', 'r', 'WITH', 'r.id = ovr.rowId')
//
//
//    }

//    /**
//     * @return OrdersVariants[] Returns an array of OrdersVariants objects
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

//    public function findOneBySomeField($value): ?OrdersVariants
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
