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

    private $options = [
        'verify' => false,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36',
            'Connection' => 'keep-alive',
            'Cache-Control' => 'no-cache',
            'Accept-Encoding' => 'gzip, deflate',
        ],
    ];

    /**
     * HttpClient constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->client = new Client(array_merge($this->options, $options));
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
                //$this->logNotice('Request have cached', func_get_args());

                //return $item->get();
            }

            $response = $this->client->request(strtoupper($method), $uri, $options);

            if (!$response) {
                return false;
            }

            $header  = $response->getHeader('Content-Type')[0];
            $content = $response->getBody()->getContents();

            if (strpos($header, 'application/json') !== false) {
                $content = json_decode($content);
            }

            $item->set($content);
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
    protected function getCrawler($method, $uri, array $options = [])
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
     * @param string $url
     * @param string $saveTo
     * @return boolean
     */
    public function download($url, $saveTo)
    {
        // Local file already exists
        if ((new Filesystem())->exists($saveTo)) {
            chmod($saveTo, 0755);
        }

        try {
            $response = $this->client->request('GET', $url, ['sink' => $saveTo]);

        } catch (GuzzleException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }

        $orgFileSize        = (int)$response->getHeader('Content-Length')[0];
        $downloadedFileSize = filesize($saveTo);

        if ($orgFileSize !== $downloadedFileSize) {
            $this->logError('Downloaded filesize is not matched remote file');

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
     * @param string $url
     * @return integer
     */
    public function getFilesize($url)
    {
        return (int)$this->client->head($url)->getHeader('Content-Length')[0];
    }
}
