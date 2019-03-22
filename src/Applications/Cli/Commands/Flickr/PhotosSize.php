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
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends AbstractCommandFlickr
{
    /**
     * @var string
     */
    private $nsid;

    /**
     * @var array
     */
    private $photos;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch photos size');
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
            'limit' => [
                'description' => 'Number of photos will be used for get sizes',
                'default' => DefinesFlickr::REST_LIMIT_PHOTOS_SIZE,
            ],
            'all' => [
                'description' => 'Fetch all photos from an NSID',
                'default' => false,
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
    }

    /**
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album) {
            return -1;
        }

        $photos = $this->flickr->flickrPhotoSetsGetPhotos($album, $this->nsid);

        if (!$photos) {
            return false;
        }

        foreach ($photos->photoset->photo as $photo) {
            $this->photos[] = $photo->id;
        }

        return 1;
    }

    /**
     * @return boolean
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds) {
            return -1;
        }

        $process = new Process(
            ['php', XGALLERY_ROOT.'/cli.php', 'flickr:photos', '--photo_ids='.$photoIds],
            null,
            null,
            null,
            DefinesCore::MAX_EXECUTE_TIME
        );
        $process->start();
        $process->wait();

        $this->photos = explode(',', $photoIds);

        return 1;
    }

    /**
     * @return boolean
     */
    protected function preparePhotosFromDb()
    {
        $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL ';

        // Specific NSID
        if ($this->nsid) {
            $this->log('Working on NSID: '.$this->nsid);
            $query .= ' AND owner = ?';
        }

        if ($this->nsid === null || ($this->nsid && !$this->getOption('all'))) {
            $query .= 'LIMIT '.(int)$this->getOption('limit').' FOR UPDATE';
        }

        try {
            if ($this->nsid) {
                $this->photos = $this->connection->executeQuery($query, [$this->nsid])->fetchAll(FetchMode::COLUMN);
            } else {
                $this->photos = $this->connection->executeQuery($query)->fetchAll(FetchMode::COLUMN);
            }

            return 1;
        } catch (DBALException $exception) {
            $this->log($exception->getMessage(), 'error');
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function processFetchSizes()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('Can not get photos from database or no photos found', 'notice');

            return false;
        }

        if (count($this->photos) > 1000) {
            $this->log('Over API. Reduced to 1000', 'notice');
            $this->photos = array_slice($this->photos, 0, 1000);
        }

        $this->log('Working on '.count($this->photos).' photos');
        $failed = 0;

        foreach ($this->photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->log('Fetching '.$photoId);

            if (!$photoSize) {
                try {
                    $this->connection->executeUpdate(
                        'UPDATE `xgallery_flickr_photos` SET `status` = ? WHERE `id` = ?',
                        [DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES, $photoId]
                    );
                } catch (DBALException $exception) {
                    $this->log($exception->getMessage(), 'error');
                }

                $this->log('Something wrong on photo_id: '.$photoId, 'notice');
                $failed++;

                continue;
            }

            try {
                $this->connection->executeUpdate(
                    'UPDATE `xgallery_flickr_photos` SET `params` = ? WHERE `id` = ?',
                    [json_encode($photoSize->sizes->size), $photoId]
                );
            } catch (DBALException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        $this->log('Failed count: '.$failed);

        return true;
    }
}
