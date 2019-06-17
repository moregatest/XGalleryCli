<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Traits;

use App\Entity\JavGenre;
use App\Entity\JavMoviesXref;

/**
 * Trait HasMovies
 * @package App\Traits
 */
trait HasMovies
{
    /**
     * @param $genres
     */
    protected function insertGenres($genres)
    {
        foreach ($genres as $genre) {
            $genreEntity = $this->entityManager->getRepository(JavGenre::class)->findOneBy(['name' => $genre]);

            if ($genreEntity) {
                continue;
            }

            $genreEntity = new JavGenre;
            $genreEntity->setName($genre);
            $this->entityManager->persist($genreEntity);
        }
    }

    /**
     * @param $genres
     * @param $movieEntity
     */
    protected function insertXRef($genres, $movieEntity)
    {
        foreach ($genres as $genre) {
            $genreEntity = $this->entityManager->getRepository(JavGenre::class)->findOneBy(
                ['name' => $genre]
            );

            if (!$genreEntity) {
                continue;
            }

            $xRefEntity = $this->entityManager->getRepository(JavMoviesXref::class)->findOneBy(
                [
                    'movie_id' => $movieEntity->getId(),
                    'xref_id' => $genreEntity->getId(),
                    'xref_type' => 'genre',
                ]
            );

            if ($xRefEntity) {
                continue;
            }

            $xRefEntity = new JavMoviesXref;
            $xRefEntity->setXrefId($genreEntity->getId());
            $xRefEntity->setXrefType('genre');
            $xRefEntity->setMovieId($movieEntity->getId());

            $this->entityManager->persist($xRefEntity);
        }

        $this->entityManager->flush();
    }
}
