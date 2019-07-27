<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\JavMyFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavMyFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavMyFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavMyFavorite[]    findAll()
 * @method JavMyFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavMyFavoriteRepository extends ServiceEntityRepository
{
    /**
     * JavMyFavoriteRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavMyFavorite::class);
    }

    // /**
    //  * @return JavMyFavorite[] Returns an array of JavMyFavorite objects
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
    public function findOneBySomeField($value): ?JavMyFavorite
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
