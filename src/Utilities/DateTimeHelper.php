<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Utilities;

use DateTime;
use Exception;

/**
 * Class DateTimeHelper
 * @package XGallery\Utilities
 */
class DateTimeHelper
{
    /**
     * Get datetime by MySQL format
     *
     * @return string
     * @throws Exception
     */
    public static function toMySql()
    {
        return (new DateTime)->format('Y-m-d H:i:s');
    }
}
