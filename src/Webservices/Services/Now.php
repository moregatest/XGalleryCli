<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use XGallery\Webservices\Restful;
use XGallery\Webservices\Services\Now\Traits\HasCollection;
use XGallery\Webservices\Services\Now\Traits\HasDelivery;
use XGallery\Webservices\Services\Now\Traits\HasDish;
use XGallery\Webservices\Services\Now\Traits\HasMerchant;
use XGallery\Webservices\Services\Now\Traits\HasMetadata;
use XGallery\Webservices\Services\Now\Traits\HasReservation;

/**
 * Class Now
 * @package XGallery\Webservices\Services
 */
class Now extends Restful
{
    use HasCollection;
    use HasDelivery;
    use HasMerchant;
    use HasReservation;
    use HasMetadata;
    use HasDish;

    const API_VERSION = 1;

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
        $options['headers'] = [
            'x-foody-api-version' => 1,
            'x-foody-app-type' => 1004,
            'x-foody-client-id' => '',
            'x-foody-client-language' => 'vi',
            'x-foody-client-type' => 1,
            'x-foody-client-version' => '3.0.0',
        ];

        $response = parent::fetch($method, $uri, $options);

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
}
