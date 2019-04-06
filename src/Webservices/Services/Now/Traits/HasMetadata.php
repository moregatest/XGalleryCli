<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Now\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Trait HasCollection
 * @package XGallery\Webservices\Services\Now\Traits
 */
trait HasMetadata
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

    public function getMetadata()
    {
        $respond = $this->fetch('POST', 'https://gappapi.tablenow.vn/api/metadata/get_metadata');

        if (!$respond) {
            return false;
        }

        return $respond->metadata;
    }

    public function getDeliveryNowMetadata()
    {
        $respond = $this->fetch('GET', 'https://gappapi.deliverynow.vn/api/meta/get_metadata');

        if (!$respond) {
            return false;
        }

        return $respond;
    }
}
