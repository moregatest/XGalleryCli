<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Psr\Cache\InvalidArgumentException;
use XGallery\Factory;

/**
 * Class Restful
 *
 * @package XGallery\Webservices
 */
class Restful extends Client
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Restful constructor.
     *
     * @param array $config
     *
     * @throws Exception
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
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function fetch($method, $uri, array $options = [])
    {
        try {
            $this->logger->info(
                __FUNCTION__,
                [$uri, $method, $options]
            );

            $response = $this->request($method, $uri, $options);

            return $response->getBody()->getContents();
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