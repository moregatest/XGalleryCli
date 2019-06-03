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
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\NctCommand;
use XGallery\Defines\DefinesCommand;
use XGallery\Defines\DefinesCore;

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
        $this->setName('nct:search')
            ->setDescription('Search NCT by conditions')
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
            [
                'title' => $this->getOption('title'),
                'singer' => $this->getOption('singer'),
            ]
        );

        if (empty($this->songs)) {
            return DefinesCommand::PREPARE_FAILED;
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
                ->find($song['href']);

            if ($nctEntity !== null) {
                $this->io->progressAdvance();
                continue;
            }

            $nctEntity = new Nct();
            $nctEntity->setUrl($song['href']);
            $nctEntity->setTitle($song['name']);
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
