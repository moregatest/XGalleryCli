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

use App\Factory;
use App\Traits\HasLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class HttpClient
 * @package App\Service
 */
class HttpClient
{
    use HasLogger;

    /**
     * @var Client
     */
    protected $client;

    /**
     * HttpClient constructor.
     * @param array $options
     */
    public function __construct($options = ['verify' => false])
    {
        $this->client = new Client($options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return boolean|string
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        try {
            $cache = Factory::getCache();
            $item  = $cache->getItem(md5(serialize(func_get_args())));

            if ($item->isHit()) {
                $this->logNotice('Request have cached', func_get_args());

                return $item->get();
            }

            $response = $this->client->request(strtoupper($method), $uri, $options);

            if (!$response) {
                return false;
            }

            $item->set($response->getBody()->getContents());
            $item->expiresAfter(86400);
            $cache->save($item);

            return $item->get();
        } catch (InvalidArgumentException | TransferException | RequestException | ConnectException | BadResponseException | ServerException $exception) {
            $this->logError($exception->getMessage(), func_get_args());

            return false;
        }
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return bool|Crawler
     * @throws GuzzleException
     */
    public function getCrawler($method, $uri, array $options = [])
    {
        $response = $this->request($method, $uri, $options);

        if (!$response) {
            return false;
        }

        return new Crawler($response);
    }

    /**
     * @param string $uri
     * @param array $options
     * @return boolean|string
     * @throws GuzzleException
     */
    public function post($uri, $options = [])
    {
        return $this->request(strtoupper(__FUNCTION__), $uri, $options);
    }

    /**
     * @param string $uri
     * @param array $options
     * @return boolean|string
     * @throws GuzzleException
     */
    public function get($uri, $options = [])
    {
        return $this->request(strtoupper(__FUNCTION__), $uri, $options);
    }

    /**
     * @param $url
     * @param $saveTo
     * @return boolean
     */
    public static function download($url, $saveTo)
    {
        if ((new Filesystem())->exists($saveTo)) {
            chmod($saveTo, 0755);
        }

        try {
            $client   = new Client;
            $response = $client->request('GET', $url, ['sink' => $saveTo]);
        } catch (GuzzleException $exception) {
            return false;
        }

        $orgFileSize        = (int)$response->getHeader('Content-Length')[0];
        $downloadedFileSize = filesize($saveTo);

        if ($orgFileSize !== $downloadedFileSize) {
            return false;
        }

        if ($response->getStatusCode() === 200) {
            return true;
        }

        return false;
    }

    /**
     * Get remote file size
     *
     * @param $url
     * @return int
     */
    public static function getFilesize($url)
    {
        $client = new Client();
        $client->head($url);

        return (int)(new Client)->head($url)->getHeader('Content-Length')[0];
    }
}
