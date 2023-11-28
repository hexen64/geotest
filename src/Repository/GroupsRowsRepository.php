<?php

namespace App\Repository;

use App\Entity\GroupsRows;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupsRows>
 *
 * @method GroupsRows|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupsRows|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupsRows[]    findAll()
 * @method GroupsRows[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupsRowsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupsRows::class);
    }

//    /**
//     * @return GroupsRows[] Returns an array of GroupsRows objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GroupsRows
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
