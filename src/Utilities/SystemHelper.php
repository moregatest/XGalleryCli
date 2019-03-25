<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Utilities;

use Symfony\Component\Process\Process;
use XGallery\Defines\DefinesCore;

/**
 * Class SystemHelper
 * @package XGallery\Utilities
 */
class SystemHelper
{
    /**
     * Wrapped method to get Process
     *
     * @param array   $cmd
     * @param integer $timeout
     * @return Process
     */
    public static function getProcess($cmd, $timeout = DefinesCore::MAX_EXECUTE_TIME)
    {
        return new Process($cmd, null, null, null, $timeout);
    }
}
