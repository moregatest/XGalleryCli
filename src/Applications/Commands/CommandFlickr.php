<?php

namespace XGallery\Applications\Commands;

use Symfony\Component\Console\Command\Command;
use XGallery\Factory;
use XGallery\Webservices\Services\Flickr;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
class CommandFlickr extends Command
{

    /**
     * @return array|boolean
     */
    protected function getContacts()
    {
        /**
         * @var Flickr $flickr
         */
        $flickr = Factory::getServices('Flickr');

        return $flickr->flickrContactsGetAll();
    }

    /**
     * @return array|boolean
     */
    protected function getPhotos($nsid)
    {
        /**
         * @var Flickr $flickr
         */
        $flickr = Factory::getServices('Flickr');

        return $flickr->flickrPeopleGetPhotos($nsid);
    }
}