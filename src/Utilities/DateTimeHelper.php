<?php

namespace XGallery\Utilities;

/**
 * Class DateTimeHelper
 * @package XGallery\Utilities
 */
class DateTimeHelper
{
    public static function toMySql()
    {
        return (new \DateTime)->format('Y-m-d H:i:s');
    }
}