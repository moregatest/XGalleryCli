<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;

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
     * Fetch my contacts
     *
     * @return boolean
     */
    protected function prepareContacts()
    {
        $this->contacts = $this->flickr->flickrContactsGetAll();

        if (!$this->contacts || empty($this->contacts)) {
            $this->log('Can not get contacts or empty', 'notice');

            return false;
        }

        $this->log("Total contacts: ".count($this->contacts));

        return true;
    }

    /**
     * @return boolean
     */
    protected function processInsertContacts()
    {
        $rows = $this->model->insertContacts($this->contacts);

        if ($rows === false) {
            $this->log('Can not insert contacts', 'error', $this->model->getErrors());

            return false;
        }

        $this->log("Affected rows: ".(int)$rows);

        return true;
    }
}
