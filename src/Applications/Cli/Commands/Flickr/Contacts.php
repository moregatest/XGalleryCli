<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

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
     * @var array
     */
    private $contacts;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch contacts from Flickr. There are no options required');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->fetchContacts()) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function fetchContacts()
    {
        $this->info(__FUNCTION__.' ...');
        $this->contacts = $this->flickr->flickrContactsGetAll();

        if (!$this->contacts || empty($this->contacts)) {
            $this->logNotice('Can not get contacts or empty');

            return false;
        }

        $this->info("Total contacts: ".count($this->contacts), [], true);

        return true;
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(['insertContacts']);
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function insertContacts()
    {
        if (!$this->contacts || empty($this->contacts)) {
            return false;
        }

        $rows = DatabaseHelper::insertRows('xgallery_flickr_contacts', $this->contacts);

        if ($rows === false) {
            $this->logError('Can not insert contacts');

            return false;
        }

        $this->info("Affected rows: ".(int)$rows);

        return true;
    }
}