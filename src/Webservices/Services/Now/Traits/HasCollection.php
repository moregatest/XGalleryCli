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
trait HasCollection
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
     * Return collections
     *
     * @param array $collectionIds
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getCollectionInfos($collectionIds = [])
    {
        if (empty($collectionIds)) {
            return false;
        }

        $respond = $this->fetch(
            'POST',
            'https://gappapi.tablenow.vn/api/collection/get_infos',
            [
                'json' => [
                    'collection_ids' => $collectionIds,
                ],
            ]
        );

        if (!$respond) {
            return false;
        }

        return $respond->collections;
    }
}
