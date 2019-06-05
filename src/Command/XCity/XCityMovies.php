<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\XCity;

use App\Entity\JavGenre;
use App\Entity\JavIdol;
use App\Entity\JavMovie;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\XCityCommand;

/**
 * Class XCityMovies
 * @package App\Command\XCity
 */
class XCityMovies extends XCityCommand
{
    const IDOLS_LIMIT = 10;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch movies of idol')
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
     * @throws GuzzleException
     */
    protected function prepareGetMovies()
    {
        $idols = $this->entityManager->getRepository(JavIdol::class)->getIdols($this->getOption('limit'));

        if (empty($idols)) {
            return self::PREPARE_FAILED;
        }

        foreach ($idols as $idol) {
            $idol->setUpdated(new DateTime());
            $this->entityManager->persist($idol);
            $this->entityManager->flush();

            $this->log('Working on idol: ' . $idol->getName());
            $this->io->newLine();

            $links = $this->client->getProfileFilmLinks('detail/' . $idol->getId());

            $this->io->progressStart(count($links));

            foreach ($links as $link) {
                /**
                 * @TODO Return entity object
                 */
                $movie = $this->client->getFilm($link);
                $this->insertMovie($movie);

                $this->io->progressAdvance();
            }
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * @param object $movie
     * @return boolean
     */
    protected function insertMovie($movie)
    {
        if (!$movie) {
            return false;
        }

        $movieEntity = $this->entityManager->getRepository(JavMovie::class)->find($movie->id);

        if (!$movieEntity) {
            $movieEntity = new JavMovie;
            $movieEntity->setItem($movie->id);
        }

        $movieEntity->setName($movie->name);

        if (isset($movie->sales_date) && $movie->sales_date && !empty($movie->sales_date)) {
            $movieEntity->setSalesDate(DateTime::createFromFormat('Y-m-d', $movie->sales_date));
        }

        if (isset($movie->release_date) && $movie->release_date && !empty($movie->release_date)) {
            $movieEntity->setReleaseDate(DateTime::createFromFormat('Y-m-d', $movie->release_date));
        }

        $movieEntity->setItemNumber($movie->item_number);
        $movieEntity->setDescription($movie->description ?? null);
        $movieEntity->setTime($movie->time);

        $this->entityManager->persist($movieEntity);
        $this->entityManager->flush();

        // Extra data

        foreach ($movie->genres as $genre) {
            $genreEntity = $this->entityManager->getRepository(JavGenre::class)->findOneBy(
                array('name' => $genre)
            );

            if ($genreEntity) {
                continue;
            }

            $genreEntity = new JavGenre;
            $genreEntity->setName($genre);
            $this->entityManager->persist($genreEntity);
            $this->entityManager->flush();
        }

        return true;
    }
}
