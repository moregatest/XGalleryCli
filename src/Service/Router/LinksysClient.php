<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Router;

use App\Service\HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Class LinksysClient
 * @package XGallery\Service
 */
class LinksysClient extends HttpClient
{

    /**
     * @return boolean|mixed|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function jNapCoreTransaction()
    {
        return $this->request(
            'POST',
            'http://192.168.1.1/JNAP/',
            [
                'headers' => ['X-JNAP-Action' => 'http://cisco.com/jnap/core/Transaction'],
                'json' => [
                    ['action' => 'http://linksys.com/jnap/devicelist/GetDevices', 'request' => new stdClass],
                ],
            ]
        );
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return boolean|mixed|ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function request($method, $uri = '', array $options = [])
    {
        $response = parent::request($method, $uri, $options);

        if (!$response) {
            $this->logNotice('Fetch failed: ' . $response);

            return false;
        }

        if ($response->result != 'OK') {
            return false;
        }

        return $response->responses;
    }
}
