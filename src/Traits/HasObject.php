<?php

namespace XGallery\Traits;

/**
 * Trait HasObject
 * @package XGallery\Traits
 */
trait HasObject
{
    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName($shortname = true)
    {
        if ($shortname) {
            $reflect = new \ReflectionClass($this);

            return $reflect->getShortName();
        }

        return get_class($this);
    }
}