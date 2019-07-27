<?php

/**
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

        $this->entityManager->flush();
    }

    /**
     * @param $genres
     * @param $movieEntity
     * @param string $type
     */
    protected function insertXRef($genres, $movieEntity, $type = 'genre')
    {
        foreach ($genres as $genre) {
            $genreEntity = $this->entityManager->getRepository(JavGenre::class)->findOneBy(['name' => $genre]);

            if (!$genreEntity) {
                continue;
            }

            $xRefEntity = $this->entityManager->getRepository(JavMoviesXref::class)->findOneBy(
                [
                    'movie_id' => $movieEntity->getId(),
                    'xref_id' => $genreEntity->getId(),
                    'xref_type' => $type,
                ]
            );

            if ($xRefEntity) {
                continue;
            }

            $xRefEntity = new JavMoviesXref;
            $xRefEntity->setXrefId($genreEntity->getId());
            $xRefEntity->setXrefType($type);
            $xRefEntity->setMovieId($movieEntity->getId());

            $this->entityManager->persist($xRefEntity);
        }

        $this->entityManager->flush();
    }
}
