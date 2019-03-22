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
use XGallery\Model\BaseModel;

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
     */
    protected function prepareContacts()
    {
        $this->contacts = $this->flickr->flickrContactsGetAll();

        if (!$this->contacts || empty($this->contacts)) {
            $this->log('Can not get contacts or empty', 'notice');

            return false;
        }

        $this->log("Total contacts: ".count($this->contacts), 'info');

        return true;
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function processInsertContacts()
    {
        $rows = BaseModel::insertRows('xgallery_flickr_contacts', $this->contacts);

        if ($rows === false) {
            $this->log('Can not insert contacts', 'error');

            return false;
        }

        $this->log("Affected rows: ".(int)$rows);

        return true;
    }
}
