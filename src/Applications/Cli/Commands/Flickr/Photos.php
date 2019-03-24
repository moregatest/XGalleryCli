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
use XGallery\Utilities\DateTimeHelper;
use XGallery\Utilities\FlickrHelper;

/**
 * Class Photos
 * Fetch all photos and insert into database
 *
 * @package XGallery\Applications\Commands\Flickr
 */
final class Photos extends AbstractCommandFlickr
{
    /**
     * @var string
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
            'gallery' => [
                'description' => 'Fetch photos from specific gallery',
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
        if ($this->nsid) {
            $this->log('Load specific NSID');

            return true;
        }

        $this->log('Load NSID from database');
        $this->nsid = $this->model->getContactNsid();

        /**
         * @TODO If NSID not found in database then insert new one
         */
        if (!$this->nsid) {
            $this->log('Can not get people from database', 'notice', $this->model->getErrors());

            return false;
        }

        return true;
    }

    /**
     * Update contact
     *
     * @return boolean|integer
     */
    protected function prepareUpdateContact()
    {
        $contact = $this->flickr->flickrPeopleGetInfo($this->nsid);

        $total = null;
        if ($contact) {
            $total = $contact->person->photos->count->_content;
        }

        return $this->model->updateContact(
            $this->nsid,
            ['modified' => DateTimeHelper::toMySql(), 'total_photos' => $total]
        );
    }

    /**
     * Get photos detail
     *
     * @return boolean
     */
    protected function processPhotos()
    {
        $photoIds = $this->getOption('photo_ids');
        $album    = $this->getOption('album');
        $gallery  = $this->getOption('gallery');

        if ((!$album && !$gallery) && $this->nsid) {
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
            $this->photos = $this->flickr->flickrPhotoSetsGetAllPhotos($album, $this->nsid);

            if ($this->photos) {
                foreach ($this->photos as $index => $photo) {
                    $photo->owner         = $this->nsid;
                    $this->photos[$index] = $photo;
                }
            }

            return true;
        }

        if ($gallery) {
            $this->log('Working on gallery: '.$gallery);
            $this->photos = $this->flickr->flickrGalleriesGetAllPhotos($gallery);

            if ($this->photos) {
                foreach ($this->photos as $index => $photo) {
                    unset($photo->has_comment);
                    $this->photos[$index] = $photo;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Insert photos into database
     *
     * @return boolean
     */
    protected function processInsertPhotos()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are not photos', 'notice');

            return false;
        }

        $this->totalPhotos = count($this->photos);
        $this->log('Total: '.$this->totalPhotos.' photos');

        $rows = $this->model->insertPhotos($this->photos);

        if ($rows === false) {
            $this->log('Can not insert photos', 'notice', $this->model->getErrors());

            return false;
        }

        $this->log("Updated ".$rows." photos into contact");

        return true;
    }
}
