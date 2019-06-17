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

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\Command\NctCommand;

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
        $this->setDescription('Fetch TOP 20');

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

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     * @throws Exception
     */
    protected function processInsertSongs()
    {
        $this->io->newLine();
        $this->io->progressStart(count($this->songs));

        foreach ($this->songs as $index => $song) {
            $this->insertEntity($song, $index);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
