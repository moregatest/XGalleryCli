<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\DeliveryNowCountry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DeliveryNowCountry|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryNowCountry|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryNowCountry[]    findAll()
 * @method DeliveryNowCountry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryNowCountryRepository extends ServiceEntityRepository
{
    /**
     * DeliveryNowCountryRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliveryNowCountry::class);
    }

    // /**
    //  * @return DeliveryNowCountry[] Returns an array of DeliveryNowCountry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliveryNowCountry
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
