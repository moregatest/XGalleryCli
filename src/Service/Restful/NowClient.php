<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Restful;

use App\Service\HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Now
 * @package App\Service\Restful
 */
class NowClient extends HttpClient
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return bool|string
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $options['headers'] = [
            'x-foody-api-version' => 1,
            'x-foody-app-type' => 1004,
            'x-foody-client-id' => '',
            'x-foody-client-language' => 'vi',
            'x-foody-client-type' => 1,
            'x-foody-client-version' => '3.0.0',
        ];

        $response = parent::request($method, $uri, $options);

        if (!$response) {
            $this->logNotice('Fetch failed: '.$response);

            return false;
        }

        $response = json_decode($response, false);

        if (!$response) {
            return false;
        }

        if ($response->result !== 'success') {
            return false;
        }

        return $response->reply;
    }

    /**
     * @return bool|string
     * @throws GuzzleException
     */
    public function getDeliveryNowMetadata()
    {
        $respond = $this->request('GET', 'https://gappapi.deliverynow.vn/api/meta/get_metadata');

        if (!$respond) {
            return false;
        }

        return $respond;
    }
}
