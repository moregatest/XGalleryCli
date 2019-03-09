<?php

namespace XGallery\Applications\Cli\Commands;

use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Factory;
use XGallery\Webservices\Services\Flickr;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandFlickr extends AbstractCommand
{
    /**
     * @var Flickr
     */
    protected $flickr;

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setName('flickr:'.strtolower($this->getClassName()));

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function prepare()
    {
        $this->flickr = Factory::getServices('flickr');

        return true;
    }
}