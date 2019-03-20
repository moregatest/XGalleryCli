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
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;

/**
 * Class PhotosDownload
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosDownload extends AbstractCommandFlickr
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
        $this->setDescription('Mass download all photos');
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
            'advanced' => [
                'description' => 'Execute advanced query to get right photos',
                'default' => null,
            ],
            'limit' => [
                'description' => 'Limit number of download',
                'default' => DefinesFlickr::DOWNLOAD_LIMIT,
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
            ['php', 'cli.php', 'flickr:photos', '--photo_ids='.$photoIds],
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
        if ($this->getOption('advanced')) {
            $this->log('Use advanced query');
            $query = 'SELECT `id` FROM xgallery_flickr_photos WHERE `status` = 0 '
                .'AND `owner` IN '
                .'( SELECT `contacts`.`nsid` FROM `xgallery_flickr_contacts` AS `contacts` '
                .'INNER JOIN( SELECT `owner`, COUNT(`id`) AS `total` FROM `xgallery_flickr_photos` WHERE `status` <> 0 GROUP BY `owner` ) AS `photos` ON `photos`.`owner` = `contacts`.`nsid` '
                .'WHERE `photos`.`total` < `contacts`.`total_photos` )';
        } else {
            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0';

            if ($this->nsid) {
                $this->log('Specific on NSID: '.$this->nsid);
                $query .= ' AND `owner` = ?';
            }
        }

        $query .= ' LIMIT '.(int)$this->getOption('limit');

        try {
            if ($this->nsid) {
                $this->photos = $this->connection->executeQuery($query, [$this->nsid])->fetchAll(FetchMode::COLUMN);
            } else {
                $this->photos = $this->connection->executeQuery($query, [])->fetchAll(FetchMode::COLUMN);
            }
        } catch (DBALException $exception) {
            $this->log($exception->getMessage(), 'error');

            return false;
        }

        return true;
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

        $processes = [];

        foreach ($this->photos as $photoId) {
            $this->log('Sending request: '.$photoId);

            try {
                $processes[$photoId] = new Process(
                    ['php', 'cli.php', 'flickr:photodownload', '--photo_id='.$photoId],
                    null,
                    null,
                    null,
                    DefinesCore::MAX_EXECUTE_TIME
                );
                $processes[$photoId]->start();
            } catch (RuntimeException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        foreach ($processes as $id => $process) {
            $this->log('Downloading '.$id.' ...');
            $process->wait();
            $this->progressBar->advance();
            $this->log('Process complete: '.$id);
        }

        return true;
    }
}