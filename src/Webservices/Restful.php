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
use Spatie\Url\Url;
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
            $id = Url::fromString($uri)
                ->withoutQueryParameter('oauth_nonce')
                ->withoutQueryParameter('oauth_signature')
                ->withoutQueryParameter('oauth_timestamp');
            $id = md5(serialize($id));

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
}
