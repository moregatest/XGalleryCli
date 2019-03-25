<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use Exception;
use ReflectionException;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Factory;
use XGallery\Utilities\DownloadHelper;
use XGallery\Utilities\SystemHelper;

/**
 * Class PhotoDownload
 * Download a specific photo
 *
 * @package XGallery\Applications\Cli\Commands\Flickr
 */
final class PhotoDownload extends AbstractCommandFlickr
{

    /**
     * Photo object
     *
     * @var stdClass
     */
    private $photo = null;

    /**
     * Photo size
     *
     * @var stdClass
     */
    private $lastSize = null;

    /**
     * Configures the current command
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Download a photo');
        $this->options = [
            'photo_id' => [
                'description' => 'Download specific photo id',
            ],
            're_download' => [
                'description' => 'Force re-download if file already exists and corrupted',
                'default' => 1,
            ],
            'no_download' => [
                'description' => 'Skip download',
            ],
        ];

        parent::configure();
    }

    /**
     * Get photo & sizes for download
     *
     * @return boolean
     */
    protected function prepareGetPhoto()
    {
        static $retry = false;

        $photoId = $this->getOption('photo_id');

        if ($photoId) {
            $this->log('Working on specific photo: '.$photoId);
        }

        if (!$this->photo = $this->model->getPhotoForDownload($photoId)) {
            $this->log('There is no photo', 'notice', $this->model->getErrors());

            return self::NEXT_PREPARE;
        }

        if ($this->photo->params === null && $retry === true) {
            $this->log('Retried but not succeed', 'notice');

            return false;
        }

        // Get photo size if needed
        if ($this->photo->params === null) {
            $this->log('Trying get photo size');
            $retry   = true;
            $process = SystemHelper::getProcess([
                'php',
                XGALLERY_ROOT.'/cli.php',
                'flickr:photossize',
                '--photo_ids='.$this->photo->id,
            ]);
            $process->start();
            $process->wait();

            return $this->preparePhoto();
        }

        return true;
    }

    /**
     * Fetch specific photo
     *
     * @return boolean
     */
    protected function prepareGetOnlinePhoto()
    {
        $photoId = $this->getOption('photo_id');

        $photo = $this->flickr->flickrPhotosGetInfo($photoId);
        $photo = $photo->photo;

        // Insert this new photo into database
        $this->photo         = new stdClass;
        $this->photo->id     = $photo->id;
        $this->photo->owner  = $photo->owner->nsid;
        $this->photo->secret = $photo->secret;
        $this->photo->server = $photo->server;
        $this->photo->farm   = $photo->farm;
        $this->photo->title  = $photo->title->_content;

        $this->model->insertPhotos([$this->photo]);

        $this->log('Trying get photo size');
        $process = SystemHelper::getProcess([
            'php',
            XGALLERY_ROOT.'/cli.php',
            'flickr:photossize',
            '--photo_ids='.$photo->id,
        ]);
        $process->run();

        return $this->prepareGetPhoto();
    }

    /**
     * Pre process photo sizes
     *
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

        /**
         * WIP
         */
        $this->event->addPhoto($this->photo);
        $dispatcher = Factory::getDispatcher();
        $dispatcher->dispatch('onPreparePhoto', $this->event);

        $verifyMedia = $this->verifyMedia();

        if ($verifyMedia !== true) {
            $this->updatePhotoStatus($this->photo->id, $verifyMedia);

            return false;
        }

        $this->log('Prepare photo succeed: '.$this->lastSize->source);

        return true;
    }

    /**
     * Validate media type
     *
     * @return boolean|integer
     */
    private function verifyMedia()
    {
        // At the moment we only download photos
        if ($this->lastSize->media !== 'photo') {
            $this->log('It\'s not media \'photo\': '.$this->lastSize->media, 'notice');

            return DefinesFlickr::PHOTO_STATUS_ERROR_NOT_PHOTO;
        }

        if ($this->lastSize->width < DefinesFlickr::FLICKR_PHOTO_MIN_WIDTH && $this->lastSize->height < DefinesFlickr::FLICKR_PHOTO_MIN_HEIGHT) {
            $this->log(
                'Photo is not matched minimum requirement: '.$this->lastSize->width.'x'.$this->lastSize->height,
                'notice'
            );

            return DefinesFlickr::PHOTO_STATUS_ERROR_NOT_MATCH_REQUIREMENT;
        }

        return true;
    }

    /**
     * Download file
     *
     * @return boolean
     * @throws Exception
     */
    protected function processDownload()
    {
        // Prepare
        $targetDir = getenv('flickr_storage').'/'.$this->photo->owner;
        $fileName  = basename($this->lastSize->source);
        $fileName  = explode('?', $fileName);
        $fileName  = $fileName[0];
        $saveTo    = $targetDir.'/'.$fileName;

        $fileSystem = new Filesystem;
        $fileSystem->mkdir($targetDir);

        $fileExists = $fileSystem->exists($saveTo);

        // File exists
        if ($fileExists) {
            $this->log('Photo already exists: '.$saveTo, 'notice');

            // Verify load and re-download if file is corrupted
            $originalFilesize = filesize($saveTo);
            $remoteFilesize   = DownloadHelper::getFilesize($this->lastSize->source);

            $this->log('Local file-size: '.$originalFilesize.' vs remote file-size: '.$remoteFilesize);

            // Than we only re-download if corrupted and re-download is required
            if ($originalFilesize != $remoteFilesize && $this->getOption('re_download') == 1) {
                $this->log('Local file is corrupted: '.$saveTo.'. Re-downloading ...', 'notice');

                if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {
                    $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                    return false;
                }

                return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);
            }

            // File exists without force re-download and local file is fine
            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ALREADY_DOWNLOADED);
        }

        if ($this->getOption('no_download') == 1) {
            $this->log('Skip download', 'notice');

            return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_SKIP_DOWNLOAD);
        }

        if (!DownloadHelper::download($this->lastSize->source, $saveTo)) {
            $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED);

            return false;
        }

        $this->log('Download completed: '.$targetDir.'/'.$fileName);

        return $this->updatePhotoStatus($this->photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);
    }

    /**
     * Update photo state
     *
     * @param $photoId
     * @param $status
     * @return boolean
     */
    protected function updatePhotoStatus($photoId, $status)
    {
        if ($this->model->updatePhoto($photoId, ['status' => $status])) {
            $this->log('State updated: '.$status);

            return true;
        }

        $this->log('Can not update photo status', 'error', $this->model->getErrors());

        return false;
    }
}
