<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Photos;

use Gumlet\ImageResize;
use ReflectionException;
use Symfony\Component\Process\Exception\RuntimeException;
use XGallery\Applications\Cli\Commands\AbstractCommandPhotos;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;
use XGallery\Utilities\SystemHelper;

/**
 * Class FlickrResizes
 * @package XGallery\Applications\Cli\Commands\Photos
 */
class FlickrResizes extends AbstractCommandPhotos
{
    /**
     * NSID
     *
     * @var string
     */
    private $nsid;

    /**
     * Array of photo id
     *
     * @var array
     */
    protected $photos;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->options = [
            'nsid' => [
                'description' => 'Resize photos from specific NSID',
            ],
            'album' => [
                'description' => 'Resize photos from specific album',
            ],
            'gallery' => [
                'description' => 'Resize photos from specific gallery URL',
            ],
            'photo_ids' => [
                'description' => 'Resize photo from specific ids',
            ],
            'limit' => [
                'default' => DefinesFlickr::RESIZE_LIMIT,
            ],
            'width' => [
                'description' => 'Resize width',
                'default' => 1920,
            ],
            'height' => [
                'description' => 'Resize height',
                'default' => 1080,
            ],
            'position' => [
                'description' => '1: Top; 2: Center; 3: Bottom; 4: Left; 5: Right',
                'default' => ImageResize::CROPCENTER,
            ],
        ];

        parent::configure();
    }

    /**
     * Validate input options
     *
     * @return boolean|integer
     */
    protected function prepareOptions()
    {
        $this->nsid = FlickrHelper::getNsid($this->getOption('nsid'));

        if ($this->getOption('album') && !$this->nsid) {
            $this->log('Missing NSID for album', 'notice');

            return false;
        }

        return true;
    }

    /**
     * Fetch photos from album
     *
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
            return self::PREPARE_FAILED;
        }

        foreach ($photos->photoset->photo as $photo) {
            $this->photos[] = $photo->id;
        }

        return self::NEXT_PREPARE;
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
     * Fetch photos from specific ids
     *
     * @return boolean
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds) {
            return self::SKIP_PREPARE;
        }

        SystemHelper::getProcess(['php', XGALLERY_ROOT.'/cli.php', 'flickr:photos', '--photo_ids='.$photoIds])->run();

        $this->photos = explode(',', $photoIds);

        return self::NEXT_PREPARE;
    }

    /**
     * Process resizes
     *
     * @return boolean
     */
    protected function processResize()
    {
        if (!$this->photos || empty($this->photos)) {
            return false;
        }

        $this->log('Total photos: '.count($this->photos));
        $limit = $this->getOption('limit');

        if (count($this->photos) > $limit) {
            $this->log('Over LIMIT. Reduced to '.$limit, 'notice');
            $this->photos = array_slice($this->photos, 0, $limit);
        }

        foreach ($this->photos as $photoId) {
            $this->log('Sending request: '.$photoId);

            try {
                SystemHelper::getProcess([
                    'php',
                    XGALLERY_ROOT.'/cli.php',
                    'photos:flickrresize',
                    '--photo_id='.$photoId,
                ])->run();
                $this->progressBar->advance();
                $this->log('Process completed: '.$photoId);
            } catch (RuntimeException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        return true;
    }
}
