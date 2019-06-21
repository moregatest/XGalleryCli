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
 * Class BatdongsanFetch
 * @package App\Command\Batdongsan
 */
final class BatdongsanFetch extends CrawlerCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch BDS data');

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function processFetch()
    {
        $this->getClient()->getAllDetailLinks(
            function ($pages) {
                $this->io->newLine();
                $this->io->progressStart($pages);
            },
            function ($links) {
                if (!$links || empty($links)) {
                    return;
                }
                foreach ($links as $link) {
                    $itemDetail = $this->getClient()->getDetail($link);

                    if (!$itemDetail) {
                        $this->logWarning('Can not extract item detail ' . $link);
                        continue;
                    }

                    $this->insertDetail($link, $itemDetail);
                }

                $this->entityManager->flush();
                $this->io->progressAdvance();
            }
        );

        return true;
    }

    /**
     * @param mixed $link
     * @param $itemDetail
     * @return boolean|void
     */
    protected function insertDetail($link, $itemDetail)
    {
        $link       = str_replace(['https://batdongsan.com.vn', 'http://batdongsan.com.vn'], '', $link);
        $itemEntity = $this->entityManager->getRepository(BatdongsanComVn::class)->find($link);

        if ($itemEntity) {
            $this->logNotice($link . ' already exists. We\'ll skip it');

            return;
        }

        $itemEntity = new BatdongsanComVn;
        $itemEntity->setUrl($link);

        $itemEntity->setName($itemDetail->price);
        $itemEntity->setSize($itemDetail->size ?? null);
        $itemEntity->setContent($itemDetail->content ?? null);
        $itemEntity->setType($itemDetail->type ?? null);
        $itemEntity->setProject($itemDetail->project ?? null);
        $itemEntity->setContactName($itemDetail->contact_name ?? null);
        $itemEntity->setPhone($itemDetail->phone ?? null);
        $itemEntity->setEmail($itemDetail->email ?? null);

        $this->entityManager->persist($itemEntity);

        return true;
    }
}
