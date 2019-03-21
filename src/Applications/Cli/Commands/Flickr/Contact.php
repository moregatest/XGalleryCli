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
use XGallery\Utilities\FlickrHelper;

/**
 * Class Contacts
 *
 * @package XGallery\Applications\Commands\Flickr
 */
class Contact extends AbstractCommandFlickr
{
    /**
     * @var array
     */
    private $contact;

    /**
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
     * @return boolean
     */
    protected function prepareContact()
    {
        $this->contact = $this->flickr->flickrPeopleGetInfo(FlickrHelper::getNsid($this->getOption('nsid')));

        if (!$this->contact) {
            $this->log('Can not get contact or empty', 'notice');

            return false;
        }

        return true;
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function processInsertContact()
    {
        if (!$this->contact) {
            return false;
        }

        $this->log('Working on NSID '.$this->contact->person->nsid);
        $contact = [
            'nsid' => $this->contact->person->nsid,
            'username' => $this->contact->person->username->_content,
            'iconserver' => $this->contact->person->iconserver,
            'iconfarm' => $this->contact->person->iconfarm,
            'ignored' => $this->contact->person->ignored,
            'realname' => $this->contact->person->realname->_content,
            'friend' => $this->contact->person->friend,
            'family' => $this->contact->person->family,
            'path_alias' => $this->contact->person->path_alias,
            'location' => $this->contact->person->location->_content,
            'total_photos' => $this->contact->person->photos->count->_content,
        ];
        $contact = json_encode($contact);
        $contact = json_decode($contact);
        $rows    = BaseModel::insertRows('xgallery_flickr_contacts', [$contact]);

        if ($rows === false) {
            $this->log('Can not insert contacts', 'error');

            return false;
        }

        $this->log("Affected rows: ".(int)$rows);

        return true;
    }
}