<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Events\Cli;

use stdClass;
use XGallery\Events\CliEvent;

/**
 * Class Flickr
 * @package XGallery\Events\Cli
 */
class Flickr extends CliEvent
{
    /**
     * Photo object
     *
     * @var stdClass
     */
    private $photo;

    /**
     * addPhoto
     *
     * @param object $photo
     */
    public function addPhoto($photo)
    {
        $this->photo = $photo;
    }
}
