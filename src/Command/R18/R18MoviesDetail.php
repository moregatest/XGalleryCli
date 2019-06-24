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

use App\Entity\JavIdol;
use App\Entity\JavMovie;
use App\Entity\JavMoviesXref;
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

    const R18_LIMIT = 500;

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

        foreach ($this->movies as $movieEntity) {
            $this->logInfo('Processing movie ' . $movieEntity->getUrl());
            $movieObj = $this->getClient()->getDetail($movieEntity->getUrl());

            if (!$movieObj) {
                $this->logWarning('Can not get movie detail');
                $this->io->progressAdvance();

                continue;
            }

            // Update movie detail
            if ($movieObj->release_date) {
                $releaseDate = DateTime::createFromFormat('F d, Y', str_replace(['.'], '', $movieObj->release_date));

                if ($releaseDate) {
                    $movieEntity->setReleaseDate($releaseDate);
                }
            }

            $movieEntity->setItemNumber($movieObj->content_id);
            $movieEntity->setUpdated(new DateTime);
            $this->entityManager->persist($movieEntity);
            $this->entityManager->flush();

            // Update genres
            $this->insertGenres($movieObj->categories);
            $this->insertXRef($movieObj->categories, $movieEntity);

            if (!empty($movieObj->actress)) {
                foreach ($movieObj->actress as $actress) {
                    $idolEntity = $this->entityManager->getRepository(JavIdol::class)->findOneBy(
                        ['name' => $actress]
                    );

                    if (!$idolEntity) {
                        $idolEntity = new JavIdol;
                        $idolEntity->setName($actress);
                        $idolEntity->setSource('r18');
                        $this->entityManager->persist($idolEntity);
                        $this->entityManager->flush();
                    }

                    $xRefEntity = $this->entityManager->getRepository(JavMoviesXref::class)->findOneBy(
                        [
                            'movie_id' => $movieEntity->getId(),
                            'xref_id' => $idolEntity->getId(),
                            'xref_type' => 'actress',
                        ]
                    );

                    if ($xRefEntity) {
                        continue;
                    }

                    $xRefEntity = new JavMoviesXref;
                    $xRefEntity->setXrefId($idolEntity->getId());
                    $xRefEntity->setXrefType('actress');
                    $xRefEntity->setMovieId($movieEntity->getId());

                    $this->entityManager->persist($xRefEntity);
                    $this->entityManager->flush();
                }
            }

            $this->io->progressAdvance();
        }

        return true;
    }
}
