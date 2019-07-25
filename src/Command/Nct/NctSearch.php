<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Nct;

use App\Command\CrawlerCommand;
use App\Entity\Nct;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class NctSearch
 * @package App\Command\Nct
 */
final class NctSearch extends CrawlerCommand
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
                        new InputOption('top20', null, InputOption::VALUE_OPTIONAL, 'Get TOP20'),
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
        if ($this->getOption('top20')) {
            $this->songs = $this->getClient()->getTop20();

            return self::PREPARE_SUCCEED;
        }

        $this->songs = $this->getClient()->search(
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

        /**
         * @TODO Skip storing database if MySQL if not started
         */
        foreach ($this->songs as $index => $song) {
            $nctEntity = $this->entityManager->getRepository(Nct::class)
                ->findOneBy(['url' => $song['href']]);

            if ($nctEntity === null) {
                $nctEntity = new Nct;
                $nctEntity->setCreated(new DateTime);
                $nctEntity->setUrl($song['href']);
                $nctEntity->setTitle($song['title']);
                $this->entityManager->persist($nctEntity);
                $this->batchInsert($nctEntity, $index);
            }

            $this->getProcess(['nct:download', '--url=' . $song['href']])->run();
            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
