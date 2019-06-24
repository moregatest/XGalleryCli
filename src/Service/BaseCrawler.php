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


use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class BaseCrawler extends HttpClient
{
    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return boolean|Crawler
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function getCrawler($method, $uri, array $options = [])
    {
        $response = $this->request($method, $uri, $options);

        if (!$response) {
            return false;
        }

        return new Crawler($response);
    }
}
