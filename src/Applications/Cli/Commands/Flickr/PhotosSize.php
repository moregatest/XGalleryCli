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
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;
use XGallery\Utilities\SystemHelper;

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
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album) {
            return self::NEXT_PREPARE;
        }

        $photos = $this->flickr->flickrPhotoSetsGetPhotos($album, $this->nsid);

        if (!$photos) {
            return self::NEXT_PREPARE;
        }

        foreach ($photos->photoset->photo as $photo) {
            $this->photos[] = $photo->id;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * @return boolean
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        // Skip
        if (!$photoIds) {
            return self::NEXT_PREPARE;
        }

        $process = SystemHelper::getProcess([
            'php',
            XGALLERY_ROOT.'/cli.php',
            'flickr:photos',
            '--photo_ids='.$photoIds,
        ]);
        $process->run();

        $this->photos = explode(',', $photoIds);

        return self::SKIP_PREPARE;
    }

    /**
     * @return boolean
     */
    protected function preparePhotosFromDb()
    {
        // Specific NSID
        if ($this->nsid) {
            $this->log('Working on NSID: '.$this->nsid);
        }

        if ($this->nsid === null || ($this->nsid && !$this->getOption('all'))) {
            $this->photos = $this->model->getPhotoIdsUnsized($this->nsid, $this->getOption('limit'));
        } else {
            $this->photos = $this->model->getPhotoIdsUnsized($this->nsid);
        }

        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice', $this->model->getErrors());

            return self::PREPARE_FAILED;
        }

        return self::NEXT_PREPARE;
    }

    /**
     * Fetching sizes
     *
     * @return boolean
     */
    protected function processFetchSizes()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('Can not get photos from database or no photos found', 'notice');

            return false;
        }

        if (count($this->photos) > DefinesCore::MAX_RESTFUL_PER_TIME) {
            $this->log('Over API. Reduced to '.DefinesCore::MAX_RESTFUL_PER_TIME, 'notice');
            $this->photos = array_slice($this->photos, 0, DefinesCore::MAX_RESTFUL_PER_TIME);
        }

        $this->log('Working on '.count($this->photos).' photos');
        $failed = 0;

        foreach ($this->photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->log('Fetching '.$photoId);

            if (!$photoSize) {
                $this->model->updatePhoto(
                    $photoId,
                    ['status' => DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES]
                );
                $this->log('Something wrong on photo_id: '.$photoId, 'notice');
                $failed++;

                continue;
            }

            $this->model->updatePhoto($photoId, ['params' => json_encode($photoSize->sizes->size)]);
        }

        $this->log('Failed count: '.$failed);

        return true;
    }
}
