<?php

namespace App\Repository;

use App\Entity\JavR18Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavR18Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavR18Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavR18Movie[]    findAll()
 * @method JavR18Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavR18MovieRepository extends ServiceEntityRepository
{
    /**
     * JavR18MovieRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavR18Movie::class);
    }

    // /**
    //  * @return JavR18Movie[] Returns an array of JavR18Movie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JavR18Movie
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
