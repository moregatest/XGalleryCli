<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Photos;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Gumlet\ImageResize;
use ReflectionException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandPhotos;
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Utilities\FlickrHelper;

/**
 * Class FlickrResizes
 * @package XGallery\Applications\Cli\Commands\Photos
 */
class FlickrResizes extends AbstractCommandPhotos
{
    /**
     * @var string
     */
    private $nsid;

    /**
     * @var array
     */
    protected $photos;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->options = [
            'nsid' => [
                'description' => 'Resize photos from specific NSID',
            ],
            'album' => [
                'description' => 'Resize photos from specific album. NSID is required to use Album',
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
        $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` > 0 AND `params` IS NOT NULL ';

        // Specific NSID
        if ($this->nsid) {
            $this->log('Working on NSID: '.$this->nsid);
            $query .= ' AND owner = ?';
        }

        $query .= ' LIMIT '.(int)$this->getOption('limit');

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
    protected function processResize()
    {
        if (!$this->photos || empty($this->photos)) {
            return false;
        }

        $this->log('Total photos: '.count($this->photos));

        if (count($this->photos) > $this->getOption('limit')) {
            $this->log('Over LIMIT. Reduced to '.$this->getOption('limit'), 'notice');
            $this->photos = array_slice($this->photos, 0, 1000);
        }

        foreach ($this->photos as $photoId) {
            $this->log('Sending request: '.$photoId);

            try {
                $process = new Process(
                    ['php', 'cli.php', 'photos:flickrresize', '--photo_id='.$photoId],
                    null,
                    null,
                    null,
                    DefinesCore::MAX_EXECUTE_TIME
                );
                $process->start();
                $process->wait();
                $this->progressBar->advance();
                $this->log('Process completed: '.$photoId);

            } catch (RuntimeException $exception) {
                $this->log($exception->getMessage(), 'error');
            }
        }

        return true;
    }
}