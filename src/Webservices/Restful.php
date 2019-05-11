<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use XGallery\Factory;
use XGallery\Traits\HasLogger;

/**
 * Class Restful
 *
 * @package XGallery\Webservices
 */
class Restful extends Client
{
    use HasLogger;

    /**
     * Wrapped method to send request
     *
     * @param string $method
     * @param string $uri
     * @param array  $options
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function fetch($method, $uri, array $options = [])
    {
        try {

            $id = md5(serialize($uri));

            $cache = Factory::getCache();
            $item  = $cache->getItem($id);

            if ($item->isHit()) {
                $this->logNotice('Request have cached', func_get_args());

                return $item->get();
            }

            $response = $this->request($method, $uri, $options);

            if (!$response) {
                return false;
            }

            $item->set($response->getBody()->getContents());
            $cache->save($item);

            return $item->get();
        } catch (RequestException $exception) {
            $this->logError(__FUNCTION__, [$uri, $method, $options]);

            if ($exception->getResponse()) {
                $this->logError(
                    $exception->getResponse()->getStatusCode(),
                    [
                        $uri,
                        $method,
                        $options,
                        $exception->getResponse()->getReasonPhrase(),
                        $exception->getResponse()->getBody()->getContents(),
                    ]
                );
            }
        }

        return false;
    }

    /**
     * GET request
     *
     * @param UriInterface|string $uri
     * @param array                                 $options
     * @return bool|mixed|ResponseInterface
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function get($uri, array $options = [])
    {
        return $this->fetch('GET', $uri, $options);
    }

    /**
     * POST request
     *
     * @param UriInterface|string $uri
     * @param array                                 $options
     * @return bool|mixed|ResponseInterface
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function post($uri, array $options = [])
    {
        return $this->fetch('POST', $uri, $options);
    }
}
