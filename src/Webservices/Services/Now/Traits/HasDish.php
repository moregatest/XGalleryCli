<?php

namespace XGallery\Webservices\Services\Now\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Trait HasDish
 * @package XGallery\Webservices\Services\Now\Traits
 */
trait HasDish
{
    /**
     * Wrapped method to send request
     *
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    abstract public function fetch($method, $uri, array $options = []);

    public function getDeliveryDishes($requestId)
    {
        $respond = $this->fetch(
            'GET',
            'https://gappapi.deliverynow.vn/api/dish/get_delivery_dishes?id_type=2&request_id='.$requestId
        );

        if (!$respond) {
            return false;
        }

        return $respond;
    }
}
