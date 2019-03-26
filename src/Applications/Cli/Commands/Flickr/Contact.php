<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use ReflectionException;
use stdClass;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Utilities\FlickrHelper;

/**
 * Class Contact
 * Insert a contact into database
 *
 * @package XGallery\Applications\Cli\Commands\Flickr
 */
final class Contact extends AbstractCommandFlickr
{
    /**
     * Contact object
     *
     * @var stdClass
     */
    private $contact;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch contact from Flickr');
        $this->options = [
            'nsid' => [
                'description' => 'Fetch by NSID',
            ],
        ];

        parent::configure();
    }

    /**
     * Fetch contact information
     *
     * @return boolean
     */
    protected function prepareContact()
    {
        $nsid = $this->getOption('nsid');

        if (!$nsid) {
            return self::PREPARE_FAILED;
        }

        $this->contact = $this->flickr->flickrPeopleGetInfo(FlickrHelper::getNsid($nsid));

        if (!$this->contact) {
            $this->log('Can not get contact or contact not found', 'notice');

            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Insert contact into database
     *
     * @return boolean
     */
    protected function processInsertContact()
    {
        $this->log('Working on NSID '.$this->contact->person->nsid);

        $contact               = new stdClass;
        $contact->nsid         = $this->contact->person->nsid;
        $contact->username     = $this->contact->person->username->_content;
        $contact->iconserver   = $this->contact->person->iconserver;
        $contact->iconfarm     = $this->contact->person->iconfarm;
        $contact->ignored      = $this->contact->person->ignored;
        $contact->realname     = $this->contact->person->realname->_content;
        $contact->friend       = $this->contact->person->friend;
        $contact->family       = $this->contact->person->family;
        $contact->path_alias   = $this->contact->person->path_alias;
        $contact->location     = $this->contact->person->location->_content;
        $contact->total_photos = $this->contact->person->photos->count->_content;

        $rows = $this->model->insertContacts([$contact]);

        if ($rows === false) {
            $this->log('Can not insert contacts', 'error', $this->model->getErrors());

            return false;
        }

        $this->log("Affected rows: ".(int)$rows);

        return true;
    }
}
