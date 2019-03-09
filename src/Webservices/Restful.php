<?php

namespace XGallery\Webservices;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use XGallery\Factory;

/**
 * Class Restful
 *
 * @package XGallery\Webservices
 */
class Restful extends Client
{

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Restful constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->logger = Factory::getLogger(get_class($this));
    }

    /**
     * @param       $method
     * @param       $uri
     * @param array $options
     *
     * @return boolean|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetch($method, $uri, array $options = [])
    {
        try {
            $cache = Factory::getCache();
            $id = md5(serialize(func_get_args()));

            $item = $cache->getItem($id);

            if ($item->isHit()) {
                $this->logger->info('Item have cached', func_get_args());

                return $item->get();
            }

            $this->logger->info(
                __FUNCTION__,
                [
                    $uri,
                    $method,
                    $options,
                ]
            );

            $response = $this->request($method, $uri, $options);

            $item->set($response->getBody()->getContents());
            $item->expiresAfter((int)getenv('cache_interval'));
            $cache->save($item);

            return $item->get();
        } catch (RequestException $exception) {
            $this->logger->error(
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

        return false;
    }
}