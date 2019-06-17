<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\R18;

use App\Entity\JavMovie;
use App\Traits\HasMovies;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\Command\R18Command;

/**
 * Class R18FetchMovies
 * @package App\Command\R18
 */
final class R18MoviesDetail extends R18Command
{
    use HasMovies;

    /**
     * @var JavMovie[]
     */
    private $movies;

    /**
     * @return bool
     */
    protected function prepareGetMovies()
    {
        $this->movies = $this->entityManager->getRepository(JavMovie::class)->findBy(
            ['source' => 'r18'],
            ['updated' => 'ASC'],
            100
        );

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processUpdateMovies()
    {
        $this->io->newLine();

        $this->io->progressStart(count($this->movies));

        foreach ($this->movies as $moveEntity) {
            $movieObj = $this->client->getMovieDetail($moveEntity->getUrl());

            if ($movieObj->release_date) {
                $releaseDate = DateTime::createFromFormat('F d, Y', str_replace(['.'], '', $movieObj->release_date));

                if ($releaseDate) {
                    $moveEntity->setReleaseDate($releaseDate);
                }
            }

            $moveEntity->setItemNumber($movieObj->content_id);
            $moveEntity->setUpdated(new DateTime);

            $this->entityManager->persist($moveEntity);
            $this->insertGenres($movieObj->categories);
            $this->entityManager->flush();

            $this->insertXRef($movieObj->categories, $moveEntity);
            $this->io->progressAdvance();
        }

        return true;
    }
}
