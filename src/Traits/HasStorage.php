<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Traits;

/**
 * Trait HasStorage
 * @package App\Traits
 */
trait HasStorage
{
    /**
     * @param $name
     * @return array|false|string
     */
    protected function getStorage($name)
    {
        $name    = strtolower($name);
        $storage = getenv('storage_' . $name);

        if ($storage) {
            return $storage;
        }

        return getenv('storage_path') . DIRECTORY_SEPARATOR . $name;
    }
}
