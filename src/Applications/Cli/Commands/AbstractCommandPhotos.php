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
use XGallery\Model\ModelFlickr;
use XGallery\Webservices\Services\Flickr;

/**
 * Class AbstractCommandPhotos
 * @package XGallery\Applications\Cli\Commands
 */
abstract class AbstractCommandPhotos extends AbstractCommand
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
     * @var ModelFlickr
     */
    protected $model;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('photos:'.strtolower($this->getClassName()));
        $this->flickr = Factory::getServices('flickr');
        $this->model  = ModelFlickr::getInstance();

        parent::configure();
    }

    /**
     * prepare
     *
     * @return boolean
     */
    protected function prepare()
    {
        parent::prepare();

        $this->flickr = Factory::getServices('flickr');

        return true;
    }

    /**
     * getNsid
     *
     * @return boolean|string|string[]|null
     */
    protected function getNsid()
    {
        $nsid = $this->getOption('nsid');

        if (filter_var($nsid, FILTER_VALIDATE_URL)) {
            $this->log('Fetching NSID from URL');

            return $this->flickr->flickrUrlsLookupUser($nsid)->user->id;
        }

        return $nsid;
    }
}
