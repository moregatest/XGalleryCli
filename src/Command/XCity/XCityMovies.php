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

use App\Entity\JavIdol;
use App\Entity\JavMovie;
use App\Traits\HasMovies;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\XCityCommand;

/**
 * Class XCityMovies
 * @package App\Command\XCity
 */
final class XCityMovies extends XCityCommand
{
    use HasMovies;
    const IDOLS_LIMIT = 100;

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
        $idols = $this->entityManager->getRepository(JavIdol::class)->findBy(
            ['source' => 'xcity'],
            ['updated' => 'ASC'],
            self::IDOLS_LIMIT
        );

        if (empty($idols)) {
            return self::PREPARE_FAILED;
        }

        foreach ($idols as $idol) {
            // Update idol
            $idol->setUpdated(new DateTime());
            // $this->entityManager->persist($idol);
            //$this->entityManager->flush();

            $this->log('Working on idol: ' . $idol->getName());
            $this->io->newLine();

            $links = $this->client->getMovieLinks('detail/' . $idol->getId());

            $this->io->progressStart(count($links));

            foreach ($links as $link) {
                /**
                 * @TODO Return entity object
                 */
                $movie       = $this->client->getMovieDetail($link);
                $movieEntity = $this->insertMovie($movie);
                $this->insertXRef($movie->genres, $movieEntity);
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

        $movieEntity = $this->entityManager->getRepository(JavMovie::class)->findOneBy(
            ['item_number' => $movie->item_number, 'source' => 'xcity']
        );

        // Movie already exists
        if ($movieEntity) {
            return $movieEntity;
        }

        $movieEntity = new JavMovie;

        $movieEntity->setName($movie->name);
        $movieEntity->setUrl($movie->url);
        $movieEntity->setSource('xcity');

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

        $this->insertGenres($movie->genres);

        $this->entityManager->flush();

        return $movieEntity;
    }
}
