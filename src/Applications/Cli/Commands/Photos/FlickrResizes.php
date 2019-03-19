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
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandPhotos;
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;

/**
 * Class FlickrResizes
 * @package XGallery\Applications\Cli\Commands\Photos
 */
class FlickrResizes extends AbstractCommandPhotos
{
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
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if ($this->loadPhotosFromAlbum()) {
            return true;
        }

        if (!$this->getPhotos()) {
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
                'resize',
            ]
        );
    }

    /**
     * @return boolean
     */
    protected function resize()
    {
        if (!$this->photos || empty($this->photos)) {
            return false;
        }

        $this->info('Total photos: '.count($this->photos));

        foreach ($this->photos as $photoId) {
            $this->info('Process '.$photoId);
            $process = new Process(
                ['php', 'cli.php', 'photos:flickrresize', '--photo_id='.$photoId],
                null,
                null,
                null,
                DefinesCore::MAX_EXECUTE_TIME
            );
            $process->start();
            $process->wait();

            $this->info('Process completed: '.$photoId, [], true);
            $this->progressBar->advance();
        }

        return true;
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function getPhotos()
    {
        $nsid = $this->getOption('nsid');

        if (!$nsid) {
            $photos = $this->getOption('photo_ids');

            if (!$photos) {
                return false;
            }

            $this->photos = explode(',', $photos);

            return true;
        }

        try {

            $this->photos = $this->connection->executeQuery(
                'SELECT `id` FROM `xgallery_flickr_photos` WHERE `owner` = ? LIMIT '.(int)$this->getOption('limit'),
                [$nsid]
            )->fetchAll(FetchMode::COLUMN);

            if (!$this->photos || empty($this->photos)) {
                $this->logNotice('There are no photos');

                return false;
            }

            $this->info('Found: '.count($this->photos).' photos', [], true);

            return true;

        } catch (Exception $exception) {

            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function loadPhotosFromAlbum()
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
}