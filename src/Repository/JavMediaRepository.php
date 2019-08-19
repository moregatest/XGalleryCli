<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\JavMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getItems($page = 1, $limit = 5)
    {
        // Create our query
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->addOrderBy('m.directory', 'ASC')
            ->getQuery();

        // No need to manually get get the result ($query->getResult())

        return $this->paginate($query, $page, $limit);
    }

    /**
     * @param $dql
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    private function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }
}
