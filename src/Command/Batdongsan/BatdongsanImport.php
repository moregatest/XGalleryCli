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
        $items = $this->client->extractItems($url);

        if (!$items || empty($items)) {
            $this->log('Can not extract items', ' notice');

            return false;
        }

        $this->log('Total items: ' . count($items));
        $skipped = 0;

        foreach ($items as $index => $item) {
            $bdsEntity = $this->entityManager->getRepository(BatdongsanComVn::class)->find($item);

            if ($bdsEntity) {
                $this->log($item . ' already exists. We\'ll skip it');
                $skipped++;

                continue;
            }

            $bdsEntity = new BatdongsanComVn;
            $bdsEntity->setUrl($item);

            $itemData = $this->client->extractItem('https://batdongsan.com.vn' . $item);

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
