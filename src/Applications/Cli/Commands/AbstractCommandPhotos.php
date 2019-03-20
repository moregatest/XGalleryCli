<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;

use Doctrine\DBAL\DBALException;
use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Factory;
use XGallery\Webservices\Services\Flickr;

/**
 * Class AbstractCommandPhotos
 * @package XGallery\Applications\Cli\Commands
 */
abstract class AbstractCommandPhotos extends AbstractCommand
{

    /**
     * @var Flickr
     */
    protected $flickr;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('photos:'.strtolower($this->getClassName()));
        $this->flickr = Factory::getServices('flickr');

        parent::configure();
    }

    /**
     * @param $status
     * @return mixed|void
     */
    protected function executeComplete($status)
    {
        $this->connection->close();

        parent::executeComplete($status);
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        $this->flickr = Factory::getServices('flickr');

        return true;
    }

    /**
     * @return bool|string|string[]|null
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