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
 * Trait HasDelivery
 * @package XGallery\Webservices\Services\Now\Traits
 */
trait HasDelivery
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

    /**
     * getBrowsingInfo
     * @uses https://www.now.vn/ Gợi ý
     *
     */
    public function getBrowsingInfo($deliveryIds = [], $position = [])
    {

    }

    /**
     * Search delivery ids
     *
     * @param $conditions
     * @return boolean|array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function searchDeliveryIds($conditions)
    {
        $respond = $this->fetch(
            'POST',
            'https://gappapi.deliverynow.vn/api/delivery/search_delivery_ids',
            ['json' => $conditions]
        );

        if (!$respond) {
            return false;
        }

        return $respond->delivery_ids;
    }

    /**
     * Get infos by ids
     *
     * @param string $ids
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getInfos($ids)
    {
        $respond = $this->fetch(
            'POST',
            'https://gappapi.deliverynow.vn/api/delivery/get_infos',
            [
                'json' => [
                    'delivery_ids' => $ids,
                ],
            ]
        );

        if (!$respond) {
            return false;
        }

        return $respond->delivery_infos;
    }

    /**
     * Get delivery detail
     *
     * @param $id
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDeliveryDetail($id)
    {
        $respond = $this->fetch(
            'GET',
            'https://gappapi.deliverynow.vn/api/delivery/get_detail?id_type=2&request_id='.$id
        );

        if (!$respond) {
            return false;
        }

        return $respond->delivery_detail;
    }

    /**
     * Search deliveries
     *
     * @param array $conditions
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function searchDeliveries($conditions)
    {
        $ids        = $this->searchDeliveryIds($conditions);
        $list       = array_chunk($ids, 25);
        $deliveries = [];

        foreach ($list as $ids) {
            $data = $this->getInfos($ids);
            if (!$data) {
                continue;
            }

            $deliveries = array_merge($deliveries, $data);
        }

        return $deliveries;
    }

    /**
     * Search detail deliveries
     *
     * @param array $conditions
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function searchDetailDeliveries($conditions)
    {
        $ids        = $this->searchDeliveryIds($conditions);
        $deliveries = [];

        foreach ($ids as $id) {
            $data = $this->getDetail($id);

            if (!$data) {
                continue;
            }

            $deliveries[] = $data;
        }

        return $deliveries;
    }
}
