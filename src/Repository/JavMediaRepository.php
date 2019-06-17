<?php

namespace App\Repository;

use App\Entity\JavMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavMedia[]    findAll()
 * @method JavMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavMediaRepository extends ServiceEntityRepository
{
    /**
     * JavMediaRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavMedia::class);
    }

    // /**
    //  * @return JavMedia[] Returns an array of JavMedia objects
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
    public function findOneBySomeField($value): ?JavMedia
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
