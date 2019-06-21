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
use Exception;
use XGallery\CrawlerCommand;

/**
 * Class R18FetchMovies
 * @package App\Command\R18
 */
final class R18MoviesDetail extends CrawlerCommand
{
    use HasMovies;

    const R18_LIMIT = 200;

    /**
     * @var JavMovie[]
     */
    private $movies;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Extract R18 movies detail');

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function prepareGetMovies()
    {
        $this->movies = $this->entityManager->getRepository(JavMovie::class)
            ->findBy(['source' => 'r18'], ['updated' => 'ASC'], self::R18_LIMIT);

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     * @throws Exception
     */
    protected function processUpdateMovies()
    {
        $this->io->newLine();
        $this->io->progressStart(count($this->movies));

        foreach ($this->movies as $moveEntity) {
            $this->logInfo('Processing movie ' . $moveEntity->getUrl());
            $movieObj = $this->getClient()->getDetail($moveEntity->getUrl());

            if (!$movieObj) {
                $this->logWarning('Can not get movie detail');

                continue;
            }

            // Update movie detail
            if ($movieObj->release_date) {
                $releaseDate = DateTime::createFromFormat('F d, Y', str_replace(['.'], '', $movieObj->release_date));

                if ($releaseDate) {
                    $moveEntity->setReleaseDate($releaseDate);
                }
            }

            $moveEntity->setItemNumber($movieObj->content_id);
            $moveEntity->setUpdated(new DateTime);
            $this->entityManager->persist($moveEntity);
            $this->entityManager->flush();

            // Update genres
            $this->insertGenres($movieObj->categories);
            $this->insertXRef($movieObj->categories, $moveEntity);
            $this->io->progressAdvance();
        }

        return true;
    }
}
