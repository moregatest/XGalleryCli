<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Nct;

use App\Entity\Nct;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\Command\NctCommand;
use XGallery\Defines\DefinesCommand;
use XGallery\Defines\DefinesCore;

/**
 * Class NctTop20
 * @package App\Command\Nct
 */
final class NctTop20 extends NctCommand
{
    /**
     * @var array
     */
    private $songs = [];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('nct:top20')
            ->setDescription('Fetch TOP 20');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function prepareTop20()
    {
        $top20 = [
            'https://www.nhaccuatui.com/bai-hat/top-20.nhac-viet.html',
            'https://www.nhaccuatui.com/bai-hat/top-20.au-my.html',
            'https://www.nhaccuatui.com/bai-hat/top-20.nhac-han.html',
        ];

        foreach ($top20 as $aTop) {
            $this->songs = array_merge($this->songs, $this->client->getTop20($aTop));
        }

        return DefinesCommand::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    protected function processInsertSongs()
    {
        $this->io->progressStart(count($this->songs));

        $batchSize = DefinesCore::BATCH_SIZE;

        foreach ($this->songs as $index => $song) {
            $nctEntity = $this->entityManager
                ->getRepository(Nct::class)
                ->find($song['url']);

            if ($nctEntity !== null) {
                $this->io->progressAdvance();
                continue;
            }

            $nctEntity = new Nct();
            $nctEntity->setUrl($song['url']);
            $nctEntity->setTitle($song['title']);
            $this->entityManager->persist($nctEntity);

            // flush everything to the database every 100 inserts
            if (($index % $batchSize) == 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        /**
         * @TODO Console output blank page before Process succeed
         */
        return true;
    }
}
