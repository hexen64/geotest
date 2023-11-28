<?php

namespace App\Repository;

use App\Entity\Rows;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rows>
 *
 * @method Rows|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rows|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rows[]    findAll()
 * @method Rows[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RowsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rows::class);
    }

//    /**
//     * @return Rows[] Returns an array of Rows objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Rows
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
