<?php

namespace App\Repository;

use App\Entity\Complects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Complects>
 *
 * @method Complects|null find($id, $lockMode = null, $lockVersion = null)
 * @method Complects|null findOneBy(array $criteria, array $orderBy = null)
 * @method Complects[]    findAll()
 * @method Complects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplectsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Complects::class);
    }

//    /**
//     * @return Complects[] Returns an array of Complects objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Complects
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
