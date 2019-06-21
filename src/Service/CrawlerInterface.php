<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service;

/**
 * Interface CrawlerInterface
 * @package App\Service\Crawler
 */
interface CrawlerInterface
{
    /**
     * Get number of pages on index URL
     *
     * @return integer
     */
    public function getIndexPages();

    /**
     * Return array of links on index URL
     *
     * @param string $url
     * @return array
     */
    public function getIndexDetailLinks($url);

    /**
     * Extract data from detail URL
     *
     * @param $url
     * @return mixed
     */
    public function getDetail($url);

    /**
     * Extract all detail URLs with callback support
     *
     * @param null $callbackPagesCount
     * @param null $callback
     * @return mixed
     */
    public function getAllDetailLinks($callbackPagesCount = null, $callback = null);

    /**
     * @param null $offset
     * @param null $limit
     * @return mixed
     */
    public function getAllDetail($offset = null, $limit = null);
}
