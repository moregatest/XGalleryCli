<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use ReflectionException;
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
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch contacts from Flickr');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function process()
    {
        $this->info('Fetching contacts ...', [], true);
        $this->progressBar->start(2);

        $contacts = $this->flickr->flickrContactsGetAll();

        if (!$contacts || empty($contacts)) {
            $this->logNotice('Can not get contacts or empty');
            $this->progressBar->finish();

            return false;
        }

        $this->progressBar->advance();

        $this->info("Total contacts: ".count($contacts));
        $this->info('Insert contacts ...');

        $rows = DatabaseHelper::insertRows('xgallery_flickr_contacts', $contacts);

        if ($rows === false) {
            $this->logError('Can not insert contacts');

            return false;
        }

        $this->progressBar->finish();
        $this->info("Affected rows: ".(int)$rows);

        return true;
    }
}