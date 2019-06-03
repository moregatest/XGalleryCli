<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Defines;


/**
 * Class DefinesCommand
 * @package XGallery\Defines
 */
class DefinesCommand
{

    /**
     * Ignore this prepare. Move to next
     */
    const NEXT_PREPARE = 1;

    /**
     * Prepare failed. Escape prepare with failed
     */
    const PREPARE_FAILED = false;

    const PREPARE_SUCCEED = true;

    /**
     * Complete prepare. Move to process directly
     */
    const SKIP_PREPARE = 2;

    const EXECUTE_SUCCEED = 0;

}
