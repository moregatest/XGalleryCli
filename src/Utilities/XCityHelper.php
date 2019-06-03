<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Utilities;

/**
 * Class XCityHelper
 * @package App\Utilities
 */
class XCityHelper
{
    /**
     * @param string $index
     * @return boolean|mixed
     */
    public static function getMonth($index)
    {
        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
        ];

        if (isset($months[$index])) {
            return $months[$index];
        }

        return false;
    }
}
