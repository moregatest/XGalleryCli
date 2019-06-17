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
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\BatdongsanCommand;

/**
 * Class BatdongsanImport
 * @package App\Command\Batdongsan
 */
final class BatdongsanImport extends BatdongsanCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Import BDS detail data');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption('url', null, InputOption::VALUE_OPTIONAL),
                ]
            )
        );

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processInsertItems()
    {
        $url = $this->getOption('url');
        $this->log('Process on page: ' . $url);

        $urls = $this->client->extractItems($url);

        if (!$urls || empty($urls)) {
            $this->log('Can not extract items', ' notice');

            return false;
        }

        $this->log('Total items: ' . count($urls));
        $skipped = 0;

        /**
         * @TODO Check URls exists in database before process
         */

        foreach ($urls as $index => $url) {
            $bdsEntity = $this->entityManager->getRepository(BatdongsanComVn::class)->find($url);

            if ($bdsEntity) {
                $this->log($url . ' already exists. We\'ll skip it');
                $skipped++;

                continue;
            }

            $bdsEntity = new BatdongsanComVn;
            $bdsEntity->setUrl($url);

            $itemData = $this->client->extractItem('https://batdongsan.com.vn' . $url);

            if (!$itemData) {
                $this->log('Can not extract item', 'notice');

                continue;
            }

            $bdsEntity->setName($itemData->price);
            $bdsEntity->setSize($itemData->size ?? null);
            $bdsEntity->setContent($itemData->content ?? null);
            $bdsEntity->setType($itemData->type ?? null);
            $bdsEntity->setProject($itemData->project ?? null);
            $bdsEntity->setContactName($itemData->contact_name ?? null);
            $bdsEntity->setPhone($itemData->phone ?? null);
            $bdsEntity->setEmail($itemData->email ?? null);

            $this->batchInsert($bdsEntity, $index);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->log('Total skipped: <options=bold>' . $skipped . '</>');

        return true;
    }
}
