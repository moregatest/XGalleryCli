<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Traits;

use App\Factory;

/**
 * Trait HasCache
 * @package App\Traits
 */
trait HasCache
{
    /**
     * @param $id
     * @param $data
     * @return boolean
     */
    protected function isHit($id, &$data)
    {
        $item = $this->getCacheItem($id);

        if ($item === false) {
            return false;
        }

        if ($item->isHit()) {
            $data = $item->get();

            return $item->isHit();
        }

        return false;
    }

    /**
     * @param $id
     * @return boolean|mixed
     */
    private function getCacheItem($id)
    {
        static $instances;

        if (isset($instances[$id])) {
            return $instances[$id];
        }

        $cache = Factory::getCache();

        if ($cache === false) {
            return false;
        }

        $instances[$id] = $cache->getItem($id);

        return $instances[$id];
    }

    /**
     * @param $id
     * @param $data
     * @param int $expire
     * @return boolean
     */
    protected function saveCache($id, $data, $expire = null)
    {
        $item = $this->getCacheItem($id);

        if ($item === false) {
            return false;
        }

        $item->set($data);
        $item->expiresAfter($expire ?? (int)getenv('cache_interval'));

        Factory::getCache()->save($item);
    }
}
