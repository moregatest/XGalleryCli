<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Batdongsan;

use App\Entity\BatdongsanComVn;
use XGallery\CrawlerCommand;

/**
 * Class BatdongsanDetail
 * @package App\Command\Batdongsan
 */
final class BatdongsanDetail extends CrawlerCommand
{
    /**
     * @var array
     */
    private $data;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch BDS detail');

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function prepareGetData()
    {
        $this->data = $this->entityManager
            ->getRepository(BatdongsanComVn::class)
            ->findBy(['name' => null], null, 100);

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    protected function processFetch()
    {
        $this->io->createProgressBar(count($this->data));

        foreach ($this->data as $itemEntity) {
            $itemDetail = $this->getClient()->getDetail($itemEntity->getUrl());

            $itemEntity->setName($itemDetail->name);
            $itemEntity->setPrice($itemDetail->price);
            $itemEntity->setSize($itemDetail->size ?? null);
            $itemEntity->setContent($itemDetail->content ?? null);
            $itemEntity->setType($itemDetail->type ?? null);
            $itemEntity->setProject($itemDetail->project ?? null);
            $itemEntity->setContactName($itemDetail->contact_name ?? null);
            $itemEntity->setPhone($itemDetail->phone ?? null);
            $itemEntity->setEmail($itemDetail->email ?? null);

            $this->entityManager->persist($itemEntity);

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();

        return true;
    }
}
