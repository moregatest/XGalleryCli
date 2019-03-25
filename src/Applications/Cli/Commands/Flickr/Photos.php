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
     * Configures the current command
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch ALL photos from a contact or by requested options');
        $this->options = [
            'nsid' => [
                'description' => 'Fetch photos from specific NSID',
            ],
            'album' => [
                'description' => 'Fetch photos from specific album URL',
            ],
            'gallery' => [
                'description' => 'Fetch photos from specific gallery URL',
            ],
            'photo_ids' => [
                'description' => 'Fetch photo from specific ids',
            ],
        ];

        parent::configure();
    }

    /**
     * Validate options
     *
     * @return boolean|integer
     */
    protected function prepareOptions()
    {
        $this->nsid = FlickrHelper::getNsid($this->getOption('nsid'));

        return self::NEXT_PREPARE;
    }

    /**
     * Fetch photos from specific album
     *
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album) {
            return self::NEXT_PREPARE;
        }

        $data = FlickrHelper::getAlbumPhotos($album);

        if (!$data['photos']) {
            $this->log('Can not get photos in album or empty', 'notice');

            return self::NEXT_PREPARE;
        }

        $this->photos = $data['photos'];
        $this->nsid   = $data['nsid'];

        foreach ($this->photos as $index => $photo) {
            $photo->owner         = $this->nsid;
            $this->photos[$index] = $photo;
        }

        $this->log('Fetched '.count($this->photos).' photos of NSID: '.$data['nsid'].' in album '.$data['album']);

        return self::SKIP_PREPARE;
    }

    /**
     * Fetch photos from specific gallery
     *
     * @return integer
     */
    protected function preparePhotosFromGallery()
    {
        $gallery = $this->getOption('gallery');

        if (!$gallery) {
            return self::NEXT_PREPARE;
        }

        $this->log('Working on gallery: '.$gallery);
        $this->photos = $this->flickr->flickrGalleriesGetAllPhotos($gallery);

        if (!$this->photos || empty($this->photos)) {
            $this->log('Can not get photos in gallery or empty', 'notice');

            return self::NEXT_PREPARE;
        }

        foreach ($this->photos as $index => $photo) {
            unset($photo->has_comment);
            $this->photos[$index] = $photo;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Fetch photos from specific ids
     *
     * @return integer
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds) {
            return self::NEXT_PREPARE;
        }

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

        return self::SKIP_PREPARE;
    }

    /**
     * Get photos from NSID
     *
     * @return boolean
     */
    protected function preparePhotosFromDatabase()
    {
        if ($this->nsid) {
            $this->log('Load specific NSID');
        } else {
            $this->log('Load NSID from database');

            if (!$this->nsid = $this->model->getContactNsid()) {
                $this->log('Can not get people from database', 'notice', $this->model->getErrors());

                return self::PREPARE_FAILED;
            }
        }

        $this->log('Working on NSID: '.$this->nsid);
        $this->photos = $this->flickr->flickrPeopleGetAllPhotos($this->nsid);

        return self::NEXT_PREPARE;
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
