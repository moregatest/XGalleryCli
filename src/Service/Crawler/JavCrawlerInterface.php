<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use stdClass;

/**
 * Interface JavCrawlerInterface
 * @package App\Service\Crawler
 */
interface JavCrawlerInterface
{
    /**
     * @param $url
     * @return integer|boolean
     */
    public function getPages($url);

    /**
     * @return array|boolean
     */
    public function getProfileLinks();

    /**
     * @param $url
     * @return stdClass
     */
    public function getProfileDetail($url);

    /**
     * @param $url
     * @return array|boolean
     */
    public function getMovieLinks($url);

    /**
     * @param $url
     * @return stdClass
     */
    public function getMovieDetail($url);
}
