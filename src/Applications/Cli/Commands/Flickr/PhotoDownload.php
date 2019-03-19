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
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->loadPhoto()) {
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
                'download',
            ]
        );
    }

    /**
     * @return boolean
     */
    protected function loadPhoto()
    {
        static $retry = false;

        $this->info('Getting photo for downloading from database/options...');

        try {
            $photoId = $this->getOption('photo_id');

            if ($photoId) {
                $this->info('Working on photo: '.$photoId);
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE id = ? LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL OR `status` = 4) LIMIT 1 FOR UPDATE';
            }

            $this->photo = $this->connection->executeQuery($query, [$photoId])->fetch(FetchMode::STANDARD_OBJECT);

            if (!$this->photo) {
                $this->logNotice('There is no photo');
                $this->output->writeln('');

                return false;
            }

            if ($this->photo->params === null && $retry === true) {
                $this->logNotice('Retried but not succeed');
                $this->output->writeln("\n".'Retried but not succeed');

                return false;
            }

            if ($this->photo->params === null) {
                $this->info('Trying get photo size');
                $retry = true;
                /**
                 * @TODO Fetch Flickr with photo ID to get data if possible then insert database
                 */
                $process = new Process(
                    ['php', 'cli.php', 'flickr:photossize', '--photo_ids='.$this->photo->id],
                    null,
                    null,
                    null,
                    DefinesCore::MAX_EXECUTE_TIME
                );
                $process->start();
                $process->wait();

                return $this->loadPhoto();
            }

            $this->info('Parsing photo media: '.$this->photo->id);
            $this->photo->params = json_decode($this->photo->params);
            $this->lastSize      = end($this->photo->params);

            /**
             * @TODO Use event for filtering
             */
            if (!$this->lastSize) {
                $this->logWarning('Can not get size');
                $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND);

                return false;
            }

            if (!$this->verifyMediaType($this->lastSize->media)) {
                $this->updatePhotoStatus($photoId, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_PHOTO);

                return false;
            }

            $this->info('Photo URL: '.$this->lastSize->source, [], true);

            return true;

        } catch (\Exception $exception) {

            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @param $media
     * @return boolean
     */
    protected function verifyMediaType($media)
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
     * @throws DBALException
     */
    protected function download()
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

        // File exists but no redownlaod required
        if ($fileSystem->exists($saveTo) && $this->getOption('force') == 0) {
            $this->logWarning('Photo already exists: '.$saveTo);
            $this->output->write("\n".'Photo already exists: '.$saveTo);

            // Verify load and redownload if file is corrupted
            $originalFilesize = filesize($saveTo);
            $remoteFilesize   = DownloadHelper::getFilesize($this->lastSize->source);

            $this->info('Local filesize: '.$originalFilesize.' vs remote filesize: '.$remoteFilesize);

            if ($originalFilesize < $remoteFilesize) {
                $this->logWarning('Local file is corrupted: '.$saveTo.'');
                $this->output->write("\n".'Local file is corrupted. Redownloading ...');

                // Redownload. Local file is broken
                if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {

                    $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                    return false;
                }

                $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);

                return true;
            }

            // File exists without force redownload and local file is fine
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ALREADY_DOWNLOADED);

            return true;
        }

        if ($this->getOption('no_download') == 1) {
            $this->logNotice('Skip download');
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_SKIP_DOWNLOAD);

            return true;
        }

        try {
            if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {

                $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                return false;
            }

            $this->info('Download completed: '.$targetDir.'/'.$fileName);
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);

            return true;

        } catch (Exception $exception) {
            $this->logNotice('Download failed');
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED);
        }

        return false;
    }

    /**
     * @param $photoId
     * @param $status
     * @return boolean
     * @throws DBALException
     */
    protected function updatePhotoStatus($photoId, $status)
    {
        try {

            if ($result = $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_photos` SET status = ? WHERE id = ?',
                array($status, $photoId)
            )) {
                $this->info('State updated: '.$status);
            }

            return true;
        } catch (Exception $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }
}