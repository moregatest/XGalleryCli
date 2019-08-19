<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service;

use App\Traits\HasCache;
use App\Traits\HasLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Url\Url;

/**
 * Class HttpClient
 * @package App\Service
 */
class HttpClient extends Client
{
    use HasLogger;
    use HasCache;

    /**
     * HttpClient constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct(
            array_merge(
                [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36',
                        'Connection' => 'keep-alive',
                        'Cache-Control' => 'no-cache',
                        'Accept-Encoding' => 'gzip, deflate',
                    ],
                ],
                $config
            )
        );
    }

    /**
     * @param UriInterface|string $uri
     * @param array $options
     * @return boolean|mixed|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function post($uri, $options = [])
    {
        return $this->request(strtoupper(__FUNCTION__), $uri, $options);
    }

    /**
     * @param $method
     * @param string $uri
     * @param array $options
     * @return boolean|mixed|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function request($method, $uri = '', array $options = [])
    {
        try {
            $uriWithoutRandom = Url::fromString($uri);
            $uriWithoutRandom = $uriWithoutRandom->withoutQueryParameter('oauth_signature')
                ->withoutQueryParameter('oauth_nonce')
                ->withoutQueryParameter('oauth_timestamp');

            $id = md5(serialize([$method, (string)$uriWithoutRandom, $options]));

            if ($this->isHit($id, $response)) {
                $this->logNotice('Request have cached', func_get_args());

                return $response;
            }

            $response = parent::request(strtoupper($method), $uri, $options);

            if (!$response) {
                return false;
            }

            /**
             * @TODO Support decode content via event
             */
            $header  = $response->getHeader('Content-Type')[0] ?? '';
            $content = $response->getBody()->getContents();

            if (strpos($header, 'application/json') !== false) {
                $content = json_decode($content);
            }

            $this->saveCache($id, $content);

            return $content;
        } catch (TransferException | RequestException | ConnectException | BadResponseException | ServerException $exception) {
            $this->logError($exception->getMessage(), func_get_args());

            return false;
        }
    }

    /**
     * @param UriInterface|string $uri
     * @param array $options
     * @return boolean|mixed|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
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
        try {
            if (!$response = parent::request('GET', $url, ['sink' => $saveTo])) {
                return false;
            }

            $orgFileSize        = (int)$response->getHeader('Content-Length')[0];
            $downloadedFileSize = filesize($saveTo);

            if ($orgFileSize !== $downloadedFileSize) {
                $this->logError('Downloaded filesize is not matched remote file');

                return false;
            }

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            return true;
        } catch (GuzzleException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }

    /**
     * Get remote file size
     *
     * @param string $url
     * @return integer
     */
    public function getFilesize($url)
    {
        return (int)$this->head($url)->getHeader('Content-Length')[0];
    }
}
