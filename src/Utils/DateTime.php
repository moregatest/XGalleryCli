<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Utils;

use Exception;

/**
 * Class DateTime
 * @package App\Utils
 */
class DateTime
{
    /**
     * @param string $format
     * @param null $date
     * @return boolean|\DateTime
     * @throws Exception
     */
    public static function getDateTime($date = null, $format = 'Y/m/d')
    {
        if ($date === null) {
            return new \DateTime;
        }

        return \DateTime::createFromFormat($format, $date);
    }
}
