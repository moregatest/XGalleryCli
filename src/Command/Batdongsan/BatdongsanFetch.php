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
                    if ($itemEntity = $this->entityManager->getRepository(BatdongsanComVn::class)->findOneBy(['url' => $link])) {
                        $this->logNotice($link . ' already exists. We\'ll skip it');

                        continue;
                    }

                    $itemEntity = new BatdongsanComVn;
                    $itemEntity->setUrl($link);
                    $this->entityManager->persist($itemEntity);
                }

                $this->entityManager->flush();
                $this->io->progressAdvance();

                // Skip merge array
                return false;
            }
        );

        return true;
    }
}
