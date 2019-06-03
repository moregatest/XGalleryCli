<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\BasicAuthentication;

use App\Service\HttpClient;
use stdClass;

/**
 * Class LinksysClient
 * @package App\Service\BasicAuthentication
 */
class LinksysClient extends HttpClient
{

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return bool|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $response = parent::request($method, $uri, $options);

        if (!$response) {
            $this->logNotice('Fetch failed: '.$response);

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
     * @return bool|string
     * @throws \GuzzleHttp\Exception\GuzzleException
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
