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
use stdClass;

/**
 * Class LinksysClient
 * @package XGallery\Service
 */
class LinksysClient extends HttpClient
{

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return boolean|string
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $response = parent::request($method, $uri, $options);

        if (!$response) {
            $this->logNotice('Fetch failed: ' . $response);

            return false;
        }

        $response = json_decode($response, false);

        if (!$response) {
            return false;
        }

        if ($response->result != 'OK') {
            return false;
        }

        return $response->responses;
    }

    /**
     * @return boolean|string
     * @throws GuzzleException
     */
    public function jNapCoreTransaction()
    {
        return $this->request(
            'POST',
            'http://192.168.1.1/JNAP/',
            [
                'headers' => ['X-JNAP-Action' => 'http://cisco.com/jnap/core/Transaction'],
                'json' => [
                    [
                        'action' => 'http://linksys.com/jnap/devicelist/GetDevices',
                        'request' => new stdClass,
                    ],
                ],
            ]
        );
    }
}