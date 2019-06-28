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

use App\Entity\FlickrContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FlickrContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlickrContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlickrContact[]    findAll()
 * @method FlickrContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlickrContactRepository extends ServiceEntityRepository
{
    /**
     * FlickrContactRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FlickrContact::class);
    }

    // /**
    //  * @return FlickrContact[] Returns an array of FlickrContact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @return FlickrContact|null
     * @throws NonUniqueResultException
     */
    public function getFreeContact(): ?FlickrContact
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.updated', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTotalContacts(): ?int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(`c`.`nsid`) AS `total`')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
