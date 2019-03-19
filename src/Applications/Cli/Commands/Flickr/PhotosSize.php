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

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends AbstractCommandFlickr
{

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
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->loadPhotos()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(
            [
                'fetchSizes',
            ]
        );
    }

    /**
     * @return boolean
     */
    protected function loadPhotos()
    {
        if ($this->loadPhotosFromIds()) {
            return true;
        }

        if ($this->loadPhotosFromAlbum()) {
            return true;
        }

        if (!$this->loadPhotosFromDb()) {
            return false;
        }

        if (!$this->photos || empty($this->photos)) {
            $this->logNotice('Can not get photos from database or no photos found');
            $this->output->writeln('Can not get photos from database or no photos found');

            return false;
        }

        if (count($this->photos) > 1000) {
            $this->logNotice('Over API. Reduced to 1000');
            $this->photos = array_slice($this->photos, 0, 1000);
        }

        $this->info('Found '.count($this->photos).' photos', [], true);

        return true;
    }

    /**
     * @return boolean
     */
    private function loadPhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds || empty($photoIds)) {
            return false;
        }

        $this->info(__FUNCTION__, [], true);
        $process = new Process(
            ['php', 'cli.php', 'flickr:photos', '--photo_ids='.$photoIds],
            null,
            null,
            null,
            DefinesCore::MAX_EXECUTE_TIME
        );
        $process->start();
        $process->wait();

        $this->photos = explode(',', $photoIds);

        return true;
    }

    /**
     * @return boolean
     */
    private function loadPhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album) {
            return false;
        }

        if (!$this->getOption('nsid')) {
            $this->logNotice('Missing NSID for album');
            $this->output->write("\n".'Missing NSID for album');

            return false;
        }

        $this->info(__FUNCTION__, [], true);
        $photos = $this->flickr->flickrPhotoSetsGetPhotos($album, $this->getNsid());

        if (!$photos) {
            return false;
        }

        foreach ($photos->photoset->photo as $photo) {

            $this->photos[] = $photo->id;
        }

        return true;
    }

    /**
     * @return boolean
     */
    private function loadPhotosFromDb()
    {
        $this->info(__FUNCTION__);

        $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL ';

        $nsid = $this->getNsid();

        // Specific NSID
        if ($nsid !== null) {
            $this->info('Working on NSID: '.$nsid);
            $query .= ' AND owner = ?';
        }

        if ($nsid === null || ($nsid && !$this->getOption('all'))) {
            $query .= 'LIMIT '.(int)$this->getOption('limit').' FOR UPDATE';
        }

        try {
            $this->photos = $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);

            return true;
        } catch (DBALException $exception) {

            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function fetchSizes()
    {
        if (!$this->photos || empty($this->photos)) {
            return false;
        }

        $failed = 0;

        foreach ($this->photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->info('Fetching '.$photoId);

            if (!$photoSize) {
                try {

                    $this->connection->executeUpdate(
                        'UPDATE `xgallery_flickr_photos` SET `status` = ? WHERE `id` = ?',
                        [DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES, $photoId]
                    );

                } catch (DBALException $exception) {
                    $this->logError($exception->getMessage());
                }

                /**
                 * @TODO Update photo status to prevent fetch it again in future
                 */
                $this->logNotice('Something wrong on photo_id: '.$photoId);
                $this->output->write(': Failed');
                $failed++;

                continue;
            }

            $this->output->write(': Succeed');

            try {

                $this->connection->executeUpdate(
                    'UPDATE `xgallery_flickr_photos` SET `params` = ? WHERE `id` = ?',
                    [json_encode($photoSize->sizes->size), $photoId]
                );

            } catch (DBALException $exception) {
                $this->logError($exception->getMessage());
            }
        }

        $this->info('Failed count: '.$failed);

        return true;
    }
}