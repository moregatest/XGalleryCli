<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\XCity;

use App\Command\CrawlerCommand;
use App\Entity\JavIdol;
use App\Entity\JavMovie;
use App\Service\Crawler\XCityCrawler;
use App\Traits\HasMovies;
use DateTime;
use Exception;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class XCityMovies
 * @package App\Command\XCity
 */
final class XCityMovies extends CrawlerCommand
{
    use HasMovies;

    const IDOLS_LIMIT = 10;

    /**
     * @var XCityCrawler
     */
    private $client;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch movies of idols')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption(
                            'limit',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Limit idols will be processed',
                            self::IDOLS_LIMIT
                        ),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * @return boolean
     * @throws Exception
     */
    protected function prepareGetMovies()
    {
        $idols = $this->entityManager->getRepository(JavIdol::class)
            ->findBy(['source' => 'xcity'], ['updated' => 'ASC'], self::IDOLS_LIMIT);

        if (empty($idols)) {
            return self::PREPARE_FAILED;
        }

        $this->client = $this->getClient('XCity');

        foreach ($idols as $idol) {
            // Update idol
            $idol->setUpdated(new DateTime());
            $this->entityManager->persist($idol);
            $this->entityManager->flush();

            $this->log('Working on idol: ' . $idol->getName() . ' - ' . $idol->getXId());

            $this->client->setProfile('detail/' . $idol->getXId() . '/');
            $this->client->getAllDetailLinks(
                function ($pages) {
                    $this->io->newLine();
                    $this->io->progressStart($pages);
                },
                function ($links) {
                    foreach ($links as $link) {
                        $this->logInfo('Working on movie: ' . $link);
                        $movieDetail = $this->client->getDetail('https://xxx.xcity.jp' . $link);

                        if (!$movieDetail) {
                            continue;
                        }

                        $this->insertXRef($movieDetail->genres, $this->insertMovie($movieDetail));
                    }

                    $this->io->progressAdvance();
                }
            );
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * @param $movieDetail
     * @return JavMovie|boolean|object|null
     * @throws Exception
     */
    protected function insertMovie($movieDetail)
    {
        if (!$movieDetail) {
            return false;
        }

        $movieEntity = $this->entityManager->getRepository(JavMovie::class)->findOneBy(
            ['item_number' => $movieDetail->item_number, 'source' => 'xcity']
        );

        // Movie already exists
        if ($movieEntity) {
            $this->insertGenres($movieDetail->genres);

            return $movieEntity;
        }

        $movieEntity = new JavMovie;

        $movieEntity->setName($movieDetail->name);
        $movieEntity->setUrl($movieDetail->url);
        $movieEntity->setSource('xcity');

        if (isset($movieDetail->sales_date) && $movieDetail->sales_date && !empty($movieDetail->sales_date)) {
            $movieEntity->setSalesDate(DateTime::createFromFormat('Y-m-d', $movieDetail->sales_date));
        }

        if (isset($movieDetail->release_date) && $movieDetail->release_date && !empty($movieDetail->release_date)) {
            $movieEntity->setReleaseDate(DateTime::createFromFormat('Y-m-d', $movieDetail->release_date));
        }

        $movieEntity->setItemNumber($movieDetail->item_number);
        $movieEntity->setDescription($movieDetail->description ?? null);
        $movieEntity->setTime($movieDetail->time);
        $movieEntity->setUpdated(new DateTime);

        $this->entityManager->persist($movieEntity);
        $this->insertGenres($movieDetail->genres);

        return $movieEntity;
    }
}
