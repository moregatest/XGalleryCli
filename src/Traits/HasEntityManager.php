<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Traits;

use Doctrine\ORM\EntityManagerInterface;
use XGallery\Defines\DefinesCore;

trait HasEntityManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param $entity
     * @param $index
     */
    protected function batchInsert($entity, $index)
    {
        $this->entityManager->persist($entity);

        // flush everything to the database every bulk inserts
        if (($index % DefinesCore::BATCH_SIZE) == 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
