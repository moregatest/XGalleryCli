<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Services\Now\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Trait HasMerchant
 * @package XGallery\Webservices\Services\Now\Traits
 */
trait HasReservation
{
    /**
     * @param       $method
     * @param       $uri
     * @param array $options
     *
     * @return boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    abstract public function fetch($method, $uri, array $options = []);

    /**
     * Return array of merchants
     *
     * @param array $merchantIds
     * @return bool
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getSpecialInfos($merchantIds = [])
    {
        if (empty($merchantIds)) {
            return false;
        }

        $respond = $this->fetch(
            'POST',
            'https://gappapi.tablenow.vn/api/reservation_item/get_special_infos',
            [
                'json' => [
                    'item_ids' => $merchantIds,
                ],
            ]
        );

        if (!$respond) {
            return false;
        }

        return $respond->merchant_infos;
    }


}