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

/**
 * Class Crawler
 * @package App\Service
 */
class Crawler extends HttpClient
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return bool|string|\Symfony\Component\DomCrawler\Crawler
     * @throws GuzzleException
     */
    public function request($method, $uri = '', array $options = [])
    {
        $response = parent::request($method, $uri, $options);

        if (!$response) {
            return false;
        }

        return new \Symfony\Component\DomCrawler\Crawler($response);
    }
}
