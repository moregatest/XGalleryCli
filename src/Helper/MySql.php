<?php

namespace XGallery\Helper;

/**
 * Class MySql
 * @package XGallery\Helper
 */
class MySql
{
    /**
     * @return string
     * @throws \Exception
     */
    public static function getCurrentDateTime()
    {
        return (new \DateTime)->format('Y-m-d H:i:s');
    }
}