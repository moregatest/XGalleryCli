<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use ReflectionException;
use Symfony\Component\Process\Exception\RuntimeException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;
use XGallery\Utilities\SystemHelper;

/**
 * Class PhotosDownload
 * Massive download photos
 *
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosDownload extends AbstractCommandFlickr
{

    /**
     * User ID
     *
     * @var string
     */
    private $nsid;

    /**
     * Array of photo IDs
     *
     * @var array
     */
    private $photos;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Mass download all photos');
        $this->options = [
            'nsid' => [
                'description' => 'Fetch photos from specific NSID',
            ],
            'album' => [
                'description' => 'Fetch photos from specific album',
            ],
            'gallery' => [
                'description' => 'Fetch photos from specific gallery',
            ],
            'photo_ids' => [
                'description' => 'Fetch photo from specific ids',
            ],
            'limit' => [
                'description' => 'Limit number of download',
                'default' => DefinesFlickr::DOWNLOAD_LIMIT,
            ],
            'all' => [
                'description' => 'Download all photos from NSID',
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

        return self::NEXT_PREPARE;
    }

    /**
     * Get photo IDs from Album
     *
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        // Skip
        if (!$album) {
            return self::NEXT_PREPARE;
        }

        $photos = FlickrHelper::getAlbumPhotos($album);

        if (!$photos || !$photos['photos']) {
            return self::NEXT_PREPARE;
        }

        foreach ($photos['photos'] as $photo) {
            $this->photos[] = $photo->id;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Get photo IDs from gallery
     *
     * @return boolean|integer
     */
    protected function preparePhotosFromGallery()
    {
        $gallery = $this->getOption('gallery');

        // Skip
        if (!$gallery) {
            return self::NEXT_PREPARE;
        }

        $photos = $this->flickr->flickrGalleriesGetAllPhotos($gallery);

        if (!$photos) {
            return self::NEXT_PREPARE;
        }

        foreach ($photos as $photo) {
            $this->photos[] = $photo->id;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Get photos from IDs
     * Call flickr:photos to get photo information
     *
     * @return boolean
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds) {
            return self::NEXT_PREPARE;
        }

        SystemHelper::getProcess(['php', XGALLERY_ROOT.'/cli.php', 'flickr:photos', '--photo_ids='.$photoIds])->run();

        $this->photos = explode(',', $photoIds);

        return self::SKIP_PREPARE;
    }

    /**
     * Get photos for download
     *
     * @return boolean
     */
    protected function preparePhotosFromDb()
    {
        if (!$this->nsid || ($this->nsid && !$this->getOption('all'))) {
            $this->photos = $this->model->getPhotoIds(0, $this->nsid, $this->getOption('limit'));
        } else {
            $this->photos = $this->model->getPhotoIds(0, $this->nsid);
        }

        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice', $this->model->getErrors());

            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    protected function processDownload()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice');

            return false;
        }

        $this->log('Working on: '.count($this->photos).' photos');
        $processes = [];

        foreach ($this->photos as $photoId) {
            $this->log('Sending request: '.$photoId);

            /**
             * @TODO Prevent flickr:photodownload query again
             */
            try {
                $processes[$photoId] = SystemHelper::getProcess([
                    'php',
                    XGALLERY_ROOT.'/cli.php',
                    'flickr:photodownload',
                    '--photo_id='.$photoId,
                ]);
                $processes[$photoId]->start();
            } catch (RuntimeException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        foreach ($processes as $id => $process) {
            $this->log('Downloading '.$id.' ...');
            $process->wait();
            $this->log('Process complete: '.$id);
        }

        return true;
    }
}
