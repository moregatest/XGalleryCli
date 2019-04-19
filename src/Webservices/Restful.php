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
     * @return boolean|string
     * @throws GuzzleException
     */
    public function fetch($method, $uri, array $options = [])
    {
        try {
            $response = $this->request($method, $uri, $options);

            return $response->getBody()->getContents();
        } catch (RequestException $exception) {
            $this->logInfo(__FUNCTION__, [$uri, $method, $options]);

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
