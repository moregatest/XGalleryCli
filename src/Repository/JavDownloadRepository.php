<?php

namespace App\Repository;

use App\Entity\JavDownload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavDownload|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavDownload|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavDownload[]    findAll()
 * @method JavDownload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavDownloadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavDownload::class);
    }

    // /**
    //  * @return JavDownload[] Returns an array of JavDownload objects
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
    public function findOneBySomeField($value): ?JavDownload
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
