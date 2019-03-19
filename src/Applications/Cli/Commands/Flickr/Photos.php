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
use XGallery\Database\DatabaseHelper;
use XGallery\Utilities\DateTimeHelper;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends AbstractCommandFlickr
{
    /**
     * @var stdClass
     */
    private $people;

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
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->loadPeople()) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function loadPeople()
    {
        try {

            if ($this->getOption('album') && !$this->getOption('nsid')) {
                $this->logNotice('Missing NSID for album');
                $this->output->writeln("\n".'Missing NSID for album');

                return false;
            }

            if ($this->getOption('album') || $this->getOption('photo_ids')) {
                return true;
            }

            /**
             * Get NSID from database
             */
            $nsid = $this->getNsid();

            if ($nsid) {
                $this->info('Load specific NSID', [], true);
                $this->people       = new stdClass;
                $this->people->nsid = $nsid;

                return true;
            } else {
                $this->info('Load NSID from database', [], true);
                $query = 'SELECT `nsid` FROM `xgallery_flickr_contacts` ORDER BY `modified` ASC LIMIT 1 FOR UPDATE';
            }

            $this->people = $this->connection->executeQuery($query, [$nsid])->fetch(FetchMode::STANDARD_OBJECT);

            /**
             * @TODO If NSID not found in database then insert new one
             */
            if (!$this->people) {
                $this->logNotice('Can not get people from database');
                $this->output->writeln("\n".'Can not get people from database');

                return false;
            }

            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET `modified` = ? WHERE nsid = ?',
                array(DateTimeHelper::toMySql(), $this->people->nsid)
            );

            return true;
        } catch (DBALException $exception) {
            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(
            [
                'fetchPhotos',
                'insertPhotos',
                'updateTotal',
            ]
        );
    }

    /**
     * @return array|boolean
     */
    protected function fetchPhotos()
    {
        $photoIds = $this->getOption('photo_ids');
        $album    = $this->getOption('album');

        if (!$photoIds && !$album && $this->people) {
            $this->info('Working on NSID: '.$this->people->nsid);
            $this->photos = $this->flickr->flickrPeopleGetAllPhotos($this->people->nsid);

            return true;
        }

        // Fetch specific photo_ids directly
        if ($photoIds) {
            $this->info('Working on specific photos: '.$photoIds);
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
            $this->people       = new stdClass();
            $this->people->nsid = $this->getNsid();

            if (!$this->people->nsid) {
                $this->output->write("\n".'NSID is required');

                return false;
            }

            $this->info('Working on NSID: '.$this->people->nsid);
            $this->info('Working on album: '.$album);
            $this->photos = $this->flickr->flickrPhotoSetsGetPhotos($album, $this->people->nsid);
            if ($this->photos) {
                $this->photos = $this->photos->photoset->photo;

                foreach ($this->photos as $index => $photo) {
                    $photo->owner         = $this->people->nsid;
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
    protected function insertPhotos()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->logNotice('There are not photos');
            $this->output->write("\n".'There are not photos');

            return false;
        }

        $this->totalPhotos = count($this->photos);
        $this->info('Total: '.$this->totalPhotos.' photos');

        $rows = DatabaseHelper::insertRows('xgallery_flickr_photos', $this->photos);

        if ($rows === false) {
            $this->logError('Can not insert photos', error_get_last());

            return false;
        }

        $this->info("Updated ".$rows." photos into contact");

        return true;
    }

    /**
     * @return boolean
     */
    protected function updateTotal()
    {
        try {

            // For album & photo_ids we won't update total_photos
            if ($this->getOption('album') || $this->getOption('photo_ids')) {
                return true;
            }
            // Update total photos
            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
                array($this->totalPhotos, $this->people->nsid)
            );

            $this->info('Updated total photos of contact: '.$this->totalPhotos);

            return true;
        } catch (DBALException $exception) {
            $this->logError($exception->getMessage());
        }

        return false;
    }
}