<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\JavMyFavoriteData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavMyFavoriteData|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavMyFavoriteData|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavMyFavoriteData[]    findAll()
 * @method JavMyFavoriteData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavMyFavoriteDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavMyFavoriteData::class);
    }

    // /**
    //  * @return JavMyFavoriteData[] Returns an array of JavMyFavoriteData objects
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
    public function findOneBySomeField($value): ?JavMyFavoriteData
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
