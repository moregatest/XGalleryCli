<?php

namespace XGallery\Webservices\Services;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Webservices\Restful;

/**
 * Class AbstractCrawler
 * @package XGallery\Webservices\Services
 * @uses    https://symfony.com/doc/current/components/dom_crawler.html
 */
abstract class AbstractCrawler extends Restful
{
    /**
     * fetch
     *
     * @param       $method
     * @param       $uri
     * @param array $options
     * @return bool|mixed|Crawler
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function fetch($method, $uri, array $options = [])
    {
        $response = parent::fetch($method, $uri, $options);

        if (!$response) {
            return false;
        }

        return new Crawler($response);
    }

    abstract public function getPages($indexUrl);
}