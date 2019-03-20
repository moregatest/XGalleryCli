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
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesCore;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Utilities\DownloadHelper;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotoDownload extends AbstractCommandFlickr
{

    /**
     * @var stdClass
     */
    private $photo = null;

    /**
     * @var stdClass
     */
    private $lastSize = null;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Download a photo');
        $this->options = [
            'photo_id' => [
                'description' => 'Download specific photo id',
                'default' => null,
            ],
            'force' => [
                'default' => 0,
                'description' => 'Force redownload even if file already exists',
            ],
            'no_download' => [
                'default' => 0,
                'description' => 'Skip download',
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function preparePhoto()
    {
        static $retry = false;

        try {
            $photoId = $this->getOption('photo_id');

            if ($photoId) {
                $this->log('Working on photo: '.$photoId);
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE id = ? LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL OR `status` = 4) LIMIT 1 FOR UPDATE';
            }

            $this->photo = $this->connection->executeQuery($query, [$photoId])->fetch(FetchMode::STANDARD_OBJECT);
        } catch (\Exception $exception) {
            $this->log($exception->getMessage(), 'error');

            return false;
        }

        if (!$this->photo) {
            $this->log('There is no photo', 'notice');

            return false;
        }

        if ($this->photo->params === null && $retry === true) {
            $this->log('Retried but not succeed', 'notice');

            return false;
        }

        if ($this->photo->params === null) {
            $this->log('Trying get photo size');
            $retry   = true;
            $process = new Process(
                ['php', 'cli.php', 'flickr:photossize', '--photo_ids='.$this->photo->id],
                null,
                null,
                null,
                DefinesCore::MAX_EXECUTE_TIME
            );
            $process->start();
            $process->wait();

            return $this->preparePhoto();
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function preparePhotoSize()
    {
        $this->photo->params = json_decode($this->photo->params);
        $this->lastSize      = end($this->photo->params);

        /**
         * @TODO Use event for filtering
         */
        if (!$this->lastSize) {
            $this->log('Can not get size', 'warning');
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND);

            return false;
        }

        if (!$this->verifyMediaType($this->lastSize->media)) {
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_PHOTO);

            return false;
        }

        $this->log('Photo URL: '.$this->lastSize->source);

        return true;
    }

    /**
     * @param $media
     * @return boolean
     */
    private function verifyMediaType($media)
    {
        // At the moment we only download photos
        if ($media !== 'photo') {
            $this->logWarning('It\'s not media \'photo\': '.$media);

            return false;
        }

        return true;
    }

    /**
     * @return boolean
     * @throws \Exception
     */
    protected function processDownload()
    {
        if ($this->lastSize === null || !$this->lastSize) {
            return false;
        }

        // Prepare
        $targetDir = getenv('flickr_storage').'/'.$this->photo->owner;
        $fileName  = basename($this->lastSize->source);
        $saveTo    = $targetDir.'/'.$fileName;

        $fileSystem = new Filesystem;
        $fileSystem->mkdir($targetDir);

        // File exists but no redownload required
        if ($fileSystem->exists($saveTo) && $this->getOption('force') == 0) {
            $this->log('Photo already exists: '.$saveTo, 'warning');

            // Verify load and redownload if file is corrupted
            $originalFilesize = filesize($saveTo);
            $remoteFilesize   = DownloadHelper::getFilesize($this->lastSize->source);

            $this->log('Local filesize: '.$originalFilesize.' vs remote filesize: '.$remoteFilesize);

            if ($originalFilesize < $remoteFilesize) {
                $this->log('Local file is corrupted: '.$saveTo.'. Redownloading ...', 'notice');

                if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {

                    $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                    return false;
                }

                return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);
            }

            // File exists without force redownload and local file is fine
            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ALREADY_DOWNLOADED);
        }

        if ($this->getOption('no_download') == 1) {
            $this->log('Skip download', 'notice');

            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_SKIP_DOWNLOAD);
        }

        try {
            if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {

                $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                return false;
            }

            $this->log('Download completed: '.$targetDir.'/'.$fileName);

            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);

        } catch (Exception $exception) {
            $this->log('Download failed', 'notice');

            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED);
        }
    }

    /**
     * @param $photoId
     * @param $status
     * @return boolean
     */
    protected function updatePhotoStatus($photoId, $status)
    {
        try {

            if ($result = $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_photos` SET status = ? WHERE id = ?',
                array($status, $photoId)
            )) {
                $this->log('State updated: '.$status);
            }

            return true;
        } catch (DBALException $exception) {
            $this->log($exception->getMessage(), 'error');

            return false;
        }
    }
}