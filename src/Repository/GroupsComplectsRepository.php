<?php

namespace App\Repository;

use App\Entity\GroupsComplects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupsComplects>
 *
 * @method GroupsComplects|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupsComplects|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupsComplects[]    findAll()
 * @method GroupsComplects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupsComplectsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupsComplects::class);
    }

//    /**
//     * @return GroupsComplects[] Returns an array of GroupsComplects objects
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

//    public function findOneBySomeField($value): ?GroupsComplects
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
