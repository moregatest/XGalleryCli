<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Database\DatabaseHelper;

/**
 * Class Contacts
 *
 * @package XGallery\Applications\Commands\Flickr
 */
class Contacts extends AbstractCommandFlickr
{

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch contacts from Flickr');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function process()
    {
        $this->info('Fetching contacts ...');

        $contacts = $this->flickr->flickrContactsGetAll();

        if (!$contacts || empty($contacts)) {
            $this->logNotice('Can not get contacts or empty');

            return false;
        }

        $this->info("Total contacts: ".count($contacts));
        $this->info('Insert contacts ...');

        $rows = DatabaseHelper::insertRows('xgallery_flickr_contacts', $contacts);

        if ($rows === false) {
            $this->logError('Can not insert contacts');

            return false;
        }

        $this->info("Affected rows: ".(int)$rows);

        return true;
    }
}