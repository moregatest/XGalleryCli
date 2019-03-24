<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Traits;

use ReflectionClass;
use ReflectionException;

/**
 * Trait HasObject
 * @package XGallery\Traits
 */
trait HasObject
{
    /**
     * Get classname with or without namespace
     *
     * @param boolean $shortname
     * @return string
     * @throws ReflectionException
     */
    public function getClassName($shortname = true)
    {
        if ($shortname) {
            $reflect = new ReflectionClass($this);

            return $reflect->getShortName();
        }

        return get_class($this);
    }
}
