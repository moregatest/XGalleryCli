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
use GuzzleHttp\Exception\GuzzleException;
use XGallery\Command\R18Command;

/**
 * Class R18InsertMovies
 * @package App\Command\R18
 */
final class R18Movies extends R18Command
{
    private $indexUrl = 'https://www.r18.com/videos/vod/movies/list/pagesize=120/price=all/sort=new/type=all/page=';

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processFetchLinks()
    {
        $pages = $this->client->getPages($this->indexUrl . '1');

        $this->io->newLine();
        $this->io->progressStart($pages);

        for ($page = 1; $page <= $pages; $page++) {
            $items = $this->client->getMovieLinks($this->indexUrl . $page . '/');

            foreach ($items as $item) {
                $javMovieEntity = $this->entityManager->getRepository(JavMovie::class)->findOneBy(
                    ['url' => $item, 'source' => 'r18']
                );

                if (!$javMovieEntity) {
                    $javMovieEntity = new JavMovie;
                }

                $javMovieEntity->setUrl($item);
                $javMovieEntity->setSource('r18');
                $this->entityManager->persist($javMovieEntity);
            }

            $this->entityManager->flush();
            $this->io->progressAdvance();
        }

        return self::PREPARE_SUCCEED;
    }
}
