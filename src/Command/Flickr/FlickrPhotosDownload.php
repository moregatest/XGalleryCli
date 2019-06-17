<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use App\Entity\FlickrPhoto;
use RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\FlickrCommand;

/**
 * Class FlickrPhotosDownload
 * @package App\Command\Flickr
 */
final class FlickrPhotosDownload extends FlickrCommand
{
    /**
     * @var string
     */
    private $nsid;

    /**
     * @var
     */
    private $photos;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download photos')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption(
                            'nsid',
                            'id',
                            InputOption::VALUE_OPTIONAL,
                            'Download photos from specific NSID'
                        ),
                        new InputOption(
                            'album',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Download photos from specific album'
                        ),
                        new InputOption(
                            'gallery',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Download photos from specific gallery'
                        ),
                        /**
                         * @TODO Support gallery ID / URL
                         */
                        new InputOption(
                            'photo_ids',
                            'pids',
                            InputOption::VALUE_OPTIONAL,
                            'Download photo from specific ids'
                        ),
                        new InputOption(
                            'limit',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Number of photos will be used for get sizes',
                            self::REST_LIMIT_PHOTOS_SIZE
                        ),
                        new InputOption(
                            'all',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch all photos from an NSID'
                        ),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * Validate options
     *
     * @return boolean|integer
     */
    protected function prepareOptions()
    {
        $this->nsid = $this->client->getNsid($this->getOption('nsid'));

        return self::PREPARE_SUCCEED;
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

        $photos = $this->client->getAlbumPhotos($album);

        if (!$photos || !$photos['photos']) {
            $this->log('Can not get or there are no photos in album', 'notice');

            return self::NEXT_PREPARE;
        }

        $this->log(
            'Working on album <options=bold>' . $photos['album'] . '</> of NSID <options=bold>' . $photos['nsid'] . '</>'
        );

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

        $photos = $this->client->flickrGalleriesGetAllPhotos($gallery);

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

        $this->getProcess(['flickr:photos', '--photo_ids=' . $photoIds])->run();

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
        /**
         * @TODO Get photos with no contact record
         */
        if (!$this->nsid || ($this->nsid && !$this->getOption('all'))) {
            $this->photos = $this->entityManager->getRepository(FlickrPhoto::class)->getSizedPhotoIds(
                $this->nsid,
                $this->getOption('limit')
            );
        } else {
            $this->photos = $this->entityManager->getRepository(FlickrPhoto::class)->getSizedPhotoIds($this->nsid);
        }

        $this->photos = array_map('current', $this->photos);

        /**
         * @TODO Support download directly onfly via NSID
         */
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice');

            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Process download
     *
     * @return boolean
     */
    protected function processDownload()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice');

            return false;
        }

        $this->log('Working on: ' . count($this->photos) . ' photos');
        $processes = [];

        foreach ($this->photos as $photoId) {
            $this->log('Sending download request <options=bold>' . $photoId . '</>');

            /**
             * @TODO Prevent flickr:photodownload query again
             */
            try {
                $processes[$photoId] = $this->getProcess(['flickr:photodownload', '--photo_id=' . $photoId]);
                $processes[$photoId]->start();
            } catch (RuntimeException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        foreach ($processes as $id => $process) {
            $this->log('Downloading ' . $id . ' ...');
            $process->wait();
            $this->log('Process complete: ' . $id);
        }

        return true;
    }
}
