<?php

namespace App\Repository;

use App\Entity\ImagesUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImagesUsers>
 *
 * @method ImagesUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImagesUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImagesUsers[]    findAll()
 * @method ImagesUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImagesUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImagesUsers::class);
    }

//    /**
//     * @return ImagesUsers[] Returns an array of ImagesUsers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ImagesUsers
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
