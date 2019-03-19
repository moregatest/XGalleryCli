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
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('flickr:'.strtolower($this->getClassName()));

        parent::configure();
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
            $this->info('Fetching NSID from URL');

            return $this->flickr->flickrUrlsLookupUser($nsid)->user->id;
        }

        return $nsid;
    }
}