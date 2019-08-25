<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\R18;

use App\Command\CrawlerCommand;
use App\Entity\JavMovie;

/**
 * Class R18Movies
 * @package App\Command\R18
 */
final class R18Movies extends CrawlerCommand
{
    /**
     * @var string
     */
    private $source = 'r18';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Extract ALL R18 movies.');

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function processFetchMovies()
    {
        $this->getClient()->getAllDetailLinks(
            function ($pages) {
                $this->io->newLine();
                $this->io->progressStart($pages);
            },
            function ($links) {
                if (empty($links)) {
                    return false;
                }

                foreach ($links as $link) {
                    $this->logInfo('Processing ' . $link);
                    $javMovieEntity = $this->entityManager
                        ->getRepository(JavMovie::class)
                        ->findOneBy(['url' => $link, 'source' => $this->source]);

                    // Movie already exists then skip it
                    if ($javMovieEntity) {
                        continue;
                    }

                    $javMovieEntity = new JavMovie;
                    $javMovieEntity->setUrl($link);
                    $javMovieEntity->setSource($this->source);
                    $this->entityManager->persist($javMovieEntity);
                }

                // @TODO Try catch
                $this->entityManager->flush();
                $this->entityManager->getConnection()->close();
                $this->io->progressAdvance();
            }
        );

        return true;
    }
}
