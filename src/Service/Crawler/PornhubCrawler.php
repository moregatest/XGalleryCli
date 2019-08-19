<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use App\Service\AbstractCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Class PornhubCrawler
 * @package App\Service\Crawler
 */
class PornhubCrawler extends AbstractCrawler
{

    /**
     * @return int|void
     */
    public function getIndexPages()
    {
    }

    /**
     * @param string $url
     * @return array|void
     */
    public function getIndexDetailLinks($url)
    {
    }

    /**
     * @param $url
     * @return bool|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        if (!$response = $this->get($url)) {
            return false;
        }

        $varPos    = strpos($response, 'var flashvars_');
        $varEnd    = strpos($response, ';', $varPos);
        $subString = substr($response, $varPos, $varEnd - $varPos);
        $subPos    = strpos($subString, '{');
        $json      = json_decode(substr($subString, $subPos - 1));

        return $json;
    }

    /**
     * @param null $callbackPagesCount
     * @param null $callback
     * @return array|void
     */
    public function getAllDetailLinks($callbackPagesCount = null, $callback = null)
    {
    }

    /**
     * @param null $offset
     * @param null $limit
     * @return array|void
     */
    public function getAllDetail($offset = null, $limit = null)
    {
    }

    /**
     * @param null $page
     * @return string|void
     */
    protected function getIndexUrl($page = null)
    {
    }
}
