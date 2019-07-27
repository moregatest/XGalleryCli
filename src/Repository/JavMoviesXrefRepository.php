<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\JavMoviesXref;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavMoviesXref|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavMoviesXref|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavMoviesXref[]    findAll()
 * @method JavMoviesXref[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavMoviesXrefRepository extends ServiceEntityRepository
{
    /**
     * JavMoviesXrefRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavMoviesXref::class);
    }

    // /**
    //  * @return JavMoviesXref[] Returns an array of JavMoviesXref objects
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
    public function findOneBySomeField($value): ?JavMoviesXref
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
