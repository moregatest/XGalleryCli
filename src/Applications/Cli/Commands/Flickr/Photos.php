<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ReflectionException;
use stdClass;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Model\BaseModel;
use XGallery\Utilities\DateTimeHelper;
use XGallery\Utilities\FlickrHelper;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends AbstractCommandFlickr
{
    /**
     * @var stdClass
     */
    private $nsid;

    /**
     * @var array
     */
    private $photos = [];

    /**
     * @var integer
     */
    private $totalPhotos = 0;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch ALL photos from a contact or by requested NSID');
        $this->options = [
            'nsid' => [
                'description' => 'Fetch photos from specific NSID',
            ],
            'album' => [
                'description' => 'Fetch photos from specific album. NSID is required to use Album',
            ],
            'photo_ids' => [
                'description' => 'Fetch photo from specific ids',
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean|integer
     */
    protected function prepareOptions()
    {
        $this->nsid = FlickrHelper::getNsid($this->getOption('nsid'));

        if ($this->getOption('album') && !$this->nsid) {
            $this->log('Missing NSID for album', 'notice');

            return false;
        }

        // Skip all prepares
        if ($this->getOption('album') || $this->getOption('photo_ids')) {
            return 1;
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function prepareNsid()
    {
        try {
            if ($this->nsid) {
                $this->log('Load specific NSID');

                return true;
            }

            $this->log('Load NSID from database');
            $this->nsid = $this->connection->executeQuery(
                'SELECT `nsid` FROM `xgallery_flickr_contacts` ORDER BY `modified` ASC LIMIT 1 FOR UPDATE'
            )->fetch(FetchMode::COLUMN);

            /**
             * @TODO If NSID not found in database then insert new one
             */
            if (!$this->nsid) {
                $this->log('Can not get people from database', 'notice');

                return false;
            }

            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET `modified` = ? WHERE nsid = ?',
                array(DateTimeHelper::toMySql(), $this->nsid)
            );

            return true;
        } catch (DBALException $exception) {
            $this->log($exception->getMessage(), 'error');
        }

        return false;
    }

    /**
     * @return array|boolean
     */
    protected function processPhotos()
    {
        $photoIds = $this->getOption('photo_ids');
        $album    = $this->getOption('album');

        if (!$album && $this->nsid) {
            $this->log('Working on NSID: '.$this->nsid);
            $this->photos = $this->flickr->flickrPeopleGetAllPhotos($this->nsid);

            return true;
        }

        // Fetch specific photo_ids directly
        if ($photoIds) {
            $this->log('Working on specific photos: '.$photoIds);
            $photos = explode(',', $photoIds);

            /**
             * We won't check database because we assumed when use photo_ids user already want to force it
             */
            // Fetch photos for inserting
            foreach ($photos as $photoId) {
                $flickrPhoto = $this->flickr->flickrPhotosGetInfo($photoId);

                if (!$flickrPhoto) {
                    continue;
                }

                $photo           = new stdClass;
                $photo->id       = $photoId;
                $photo->owner    = $flickrPhoto->photo->owner->nsid;
                $photo->secret   = $flickrPhoto->photo->secret;
                $photo->server   = $flickrPhoto->photo->server;
                $photo->farm     = $flickrPhoto->photo->farm;
                $photo->title    = $flickrPhoto->photo->title->_content;
                $photo->ispublic = $flickrPhoto->photo->visibility->ispublic;
                $photo->isfriend = $flickrPhoto->photo->visibility->isfriend;
                $photo->isfamily = $flickrPhoto->photo->visibility->isfamily;

                $this->photos[] = $photo;
            }

            return true;
        }

        if ($album) {
            $this->log('Working on NSID: '.$this->nsid);
            $this->log('Working on album: '.$album);
            $this->photos = $this->flickr->flickrPhotoSetsGetPhotos($album, $this->nsid);

            if ($this->photos) {
                $this->photos = $this->photos->photoset->photo;

                foreach ($this->photos as $index => $photo) {
                    $photo->owner         = $this->nsid;
                    $this->photos[$index] = $photo;
                }
            }

            return true;
        }
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function processInsertContacts()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are not photos', 'notice');

            return false;
        }

        $this->totalPhotos = count($this->photos);
        $this->log('Total: '.$this->totalPhotos.' photos');

        $rows = BaseModel::insertRows('xgallery_flickr_photos', $this->photos);

        if ($rows === false) {
            $this->log('Can not insert photos', 'notice', error_get_last());

            return false;
        }

        $this->log("Updated ".$rows." photos into contact");

        return true;
    }

    /**
     * @return boolean
     */
    protected function processUpdateTotal()
    {
        // For album & photo_ids we won't update total_photos
        if ($this->getOption('album') || $this->getOption('photo_ids')) {
            return true;
        }

        try {
            // Update total photos
            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
                array($this->totalPhotos, $this->nsid)
            );

            $this->log('Updated total photos of contact: '.$this->totalPhotos);

            return true;
        } catch (DBALException $exception) {
            $this->log($exception->getMessage(), 'error');
        }

        return false;
    }
}
