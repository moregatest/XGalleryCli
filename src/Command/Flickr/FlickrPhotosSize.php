<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use App\Command\FlickrCommand;
use App\Entity\FlickrPhoto;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class FlickrPhotosSize
 * @package App\Command\Flickr
 */
final class FlickrPhotosSize extends FlickrCommand
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
     * @var string[]
     */
    private $photos;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch photo\'s sizes for download')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('nsid', 'id', InputOption::VALUE_OPTIONAL, 'Fetch photos from specific NSID'),
                        new InputOption(
                            'album',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific album URL'
                        ),
                        /**
                         * @TODO Support gallery ID / URL
                         */
                        new InputOption(
                            'photo_ids',
                            'pids',
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photo from specific ids'
                        ),
                        new InputOption(
                            'limit',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Number of photos will be used for get sizes',
                            self::PHOTOS_SIZE_LIMIT
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

        return self::NEXT_PREPARE;
    }

    /**
     * Get photo sizes from Album
     *
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album || !filter_var($album, FILTER_VALIDATE_URL)) {
            $this->log('There is no album provided or invalid URL', 'notice');

            return self::NEXT_PREPARE;
        }

        $photos = $this->client->getAlbumPhotos($album);

        if (!$photos && (!$photos['photos'] || empty($photos['photos']))) {
            return self::NEXT_PREPARE;
        }

        foreach ($photos['photos'] as $photo) {
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

        // Skip
        if (!$photoIds) {
            return self::NEXT_PREPARE;
        }

        $this->getProcess(['flickr:photos', '--photo_ids=' . $photoIds])->run();

        $this->photos = explode(',', $photoIds);

        return self::SKIP_PREPARE;
    }

    /**
     * Get photos from database
     *
     * @return boolean
     */
    protected function preparePhotosFromDb()
    {
        // Specific NSID
        if ($this->nsid) {
            $this->log('Working on NSID: ' . $this->nsid);
        }

        /**
         * @TODO Lock all these photos until sized
         */
        if ($this->nsid === null || ($this->nsid && !$this->getOption('all'))) {
            $this->photos = $this->entityManager->getRepository(FlickrPhoto::class)->getFreePhotoIds(
                $this->nsid,
                $this->getOption('limit')
            );
        } else {
            $this->photos = $this->entityManager->getRepository(FlickrPhoto::class)->getFreePhotoIds($this->nsid);
        }

        $this->photos = array_map('current', $this->photos);

        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice');
            $this->log('Trying to fetch photos from NSID: ' . $this->nsid);

            $this->getProcess(['flickr:photos', '--nsid=' . $this->nsid])->run();

            $this->preparePhotosFromDb();
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Execute fetching sizes
     *
     * @return boolean
     */
    protected function processFetchSizes()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('Can not get photos from database or no photos found', 'notice');

            return false;
        }

        if (count($this->photos) > 3600) {
            $this->log('Over API. Reduced to 3600', 'notice');
            $this->photos = array_slice($this->photos, 0, 3600);
        }

        $this->logInfo('Working on ' . count($this->photos) . ' photos');
        $failed = 0;

        $this->io->progressStart(count($this->photos));

        foreach ($this->photos as $index => $photoId) {
            $photoSize = $this->client->flickrPhotosSizes($photoId);

            $photoEntity = $this->entityManager->getRepository(FlickrPhoto::class)->find($photoId);

            // Photo id not exists in database. Use :photos for fetching onfly
            if (!$photoEntity) {
                $this->getProcess(
                    [
                        'flickr:photos',
                        '--photo_ids=' . $photoId,
                    ]
                )->run();

                $photoEntity = $this->entityManager->getRepository(FlickrPhoto::class)->find($photoId);

                if (!$photoEntity) {
                    $this->log('Can not fetch photo then no size can fetch too: ' . $photoId, 'notice');

                    continue;
                }
            }

            if (!$photoSize) {
                $photoEntity->setStatus(self::PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES);

                /**
                 * @TODO Send email with failed case
                 */
                $this->log('Something wrong on photo ' . $photoId . ': Can not get sizes', 'notice');
                $failed++;

                $this->entityManager->persist($photoEntity);
                $this->io->progressAdvance();

                continue;
            }

            $lastSize = end($photoSize->sizes->size);
            $photoEntity->setUrls(json_encode($photoSize->sizes->size));
            $photoEntity->setWidth($lastSize->width ?? null);
            $photoEntity->setHeight($lastSize->height ?? null);
            $photoEntity->setMedia($lastSize->media == 'photo' ? 1 : 0);
            $photoEntity->setUrl($lastSize->source);

            $this->batchInsert($photoEntity, $index);

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        if ($failed) {
            $this->log('Failed count: ' . $failed, 'notice', [], true);
        }

        return true;
    }
}
