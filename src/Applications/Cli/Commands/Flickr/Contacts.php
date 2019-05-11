<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;

/**
 * Class Contacts
 * Insert all contacts from current user into database
 *
 * @package XGallery\Applications\Cli\Commands\Flickr
 */
final class Contacts extends AbstractCommandFlickr
{
    /**
     * Array of contact object
     *
     * @var array
     */
    private $contacts;

    /**
     * Configures the current command.
     *
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

            return self::PREPARE_FAILED;
        }

        $this->log('Total contacts: '.count($this->contacts));

        return self::PREPARE_SUCCEED;
    }

    /**
     * Insert all contacts
     *
     * @return boolean
     */
    protected function processInsertContacts()
    {
        $rows = $this->model->insertContacts($this->contacts);

        if ($rows === false) {
            $this->log('Can not insert contacts', 'error', $this->model->getErrors());

            return false;
        }

        return true;
    }
}
