<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;

use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Factory;
use XGallery\Model\Flickr\ModelFlickr;
use XGallery\Webservices\Services\Flickr;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandFlickr extends AbstractCommand
{
    /**
     * Flickr service class
     *
     * @var Flickr
     */
    protected $flickr;

    /**
     * Flickr model
     *
     * @var FlickrModel
     */
    protected $model;

    /**
     * Event class
     *
     * @var \XGallery\Events\Cli\Flickr
     */
    protected $event;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('flickr:'.strtolower($this->getClassName()));
        $this->flickr = Factory::getServices('flickr');
        $this->model  = ModelFlickr::getInstance();
        $this->event  = new \XGallery\Events\Cli\Flickr;

        parent::configure();
    }
}
