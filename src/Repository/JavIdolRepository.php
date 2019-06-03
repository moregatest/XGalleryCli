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

use App\Entity\JavIdol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class JavIdolRepository
 * @package App\Repository
 */
class JavIdolRepository extends ServiceEntityRepository
{
    /**
     * JavIdolRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavIdol::class);
    }

    /**
     * @return JavIdol|null
     * @throws NonUniqueResultException
     */
    public function getIdol(): ?JavIdol
    {
        return $this->createQueryBuilder('javIdol')
            ->orderBy('javIdol.updated', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $limit
     * @return JavIdol[]|null
     */
    public function getIdols($limit = 10)
    {
        return $this->createQueryBuilder('javIdol')
            ->orderBy('javIdol.updated', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
