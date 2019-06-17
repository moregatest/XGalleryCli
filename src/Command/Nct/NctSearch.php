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
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\NctCommand;

/**
 * Class NctSearch
 * @package App\Command\Nct
 */
final class NctSearch extends NctCommand
{
    /**
     * @var array
     */
    private $songs;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Search NCT by conditions')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('title', null, InputOption::VALUE_OPTIONAL, 'Search by keyword'),
                        new InputOption('singer', null, InputOption::VALUE_OPTIONAL, 'Search by singer'),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function prepareSongs()
    {
        $this->songs = $this->client->search(
            ['title' => $this->getOption('title'), 'singer' => $this->getOption('singer')]
        );

        if (empty($this->songs)) {
            return self::PREPARE_FAILED;
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
