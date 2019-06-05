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

use App\Entity\FlickrPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FlickrPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlickrPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlickrPhoto[]    findAll()
 * @method FlickrPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlickrPhotoRepository extends ServiceEntityRepository
{
    /**
     * FlickrPhotoRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FlickrPhoto::class);
    }

    // /**
    //  * @return FlickrPhoto[] Returns an array of FlickrPhoto objects
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
     * @return FlickrPhoto|null
     * @throws NonUniqueResultException
     */
    public function getFreePhoto(): ?FlickrPhoto
    {
        return $this->createQueryBuilder('p')
            ->where('p.urls IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get photo IDs without URLs
     *
     * @param null $nsid
     * @param null $limit
     * @return mixed
     */
    public function getFreePhotoIds($nsid = null, $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.urls IS NULL')
            ->andWhere('p.status IS NULL');

        if ($nsid) {
            $queryBuilder->andWhere('p.owner = :nsid')
                ->setParameter('nsid', $nsid);
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * Get photo IDs without URLs
     *
     * @param null $nsid
     * @param null $limit
     * @return mixed
     */
    public function getSizedPhotoIds($nsid = null, $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.url IS NOT NULL')
            ->andWhere('p.status IS NULL');

        if ($nsid) {
            $queryBuilder->andWhere('p.owner = :nsid')
                ->setParameter('nsid', $nsid);
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
