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
     * @param $conditions
     * @return boolean|array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function searchDeliveryIds($conditions)
    {
        $conditions = array_merge($conditions, ['keyword' => '']);

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
     * @param $ids
     * @return bool
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
     * @param $id
     * @return bool
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($id)
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
     * @param $conditions
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
     * @param $conditions
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
