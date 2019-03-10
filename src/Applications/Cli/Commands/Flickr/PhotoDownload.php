<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use GuzzleHttp\Exception\GuzzleException;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
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
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws ConnectionException
     * @throws DBALException
     * @throws GuzzleException
     */
    protected function process()
    {
        $this->info('Getting photo ...', [], true);
        $this->progressBar->start(2);

        if ($photoId = $this->input->getOption('photo_id')) {
            $this->info('Getting photo ID: '.$photoId.' ...');
        }

        $this->connection->beginTransaction();
        $photo = $this->getPhoto($photoId);
        $this->progressBar->advance();

        // There are no photos
        if (!$photo) {
            $this->logNotice('There is no photo');

            return false;
        }

        $this->info('Work on photo: '.$photo->id);

        $size = json_decode($photo->params);
        $lastSize = end($size);

        if (!$lastSize) {
            $this->logWarning('Can not get size');

            $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_FOUND);

            return false;
        }

        if (!$this->verifyMediaType($lastSize->media, $photo->id)) {
            return false;
        }

        $this->info('Photo URL: '.$lastSize->source);

        $targetDir = getenv('flickr_storage').'/'.$photo->owner;
        (new Filesystem())->mkdir($targetDir);

        $fileName = basename($lastSize->source);
        $saveTo = $targetDir.'/'.$fileName;
        $fileExists = (new Filesystem())->exists($saveTo);

        if ($fileExists && $this->input->getOption('force') == 0) {
            $this->logWarning('Photo already exists: '.$saveTo);
            $this->output->write("\n".'Photo already exists: '.$saveTo);

            // Verify load and redownload if file is corrupted
            $originalFilesize = filesize($saveTo);
            $remoteFilesize = DownloadHelper::getFilesize($lastSize->source);

            $this->info('Local filesize: '.$originalFilesize.' vs remote filesize: '.$remoteFilesize);

            if ($originalFilesize < $remoteFilesize) {
                $this->logWarning('Local file is corrupted: '.$saveTo);
                $this->output->write("\n".'Local file is corrupted');

                // Redownload. Local file is broken
                if (!DownloadHelper::download($lastSize->source, $saveTo)) {

                    $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);
                    $this->progressBar->finish();

                    return false;
                }

                $this->progressBar->finish();
                $this->info('Redownloaded success: '.filesize($saveTo));
                $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);

                return true;
            }

            // File exists without force redownload and local file is fine
            $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ALREADY_DOWNLOADED);
            $this->progressBar->finish();

            return true;
        }

        // Skip download
        if ($this->input->getOption('no_download') == 0) {
            try {

                $this->info('Downloading photo ...');

                if (!DownloadHelper::download($lastSize->source, $saveTo)) {

                    $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);

                    return false;
                }

                $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_DOWNLOADED);
                $this->progressBar->finish();
                $this->info('Download completed: '.$targetDir.'/'.$fileName);

                return true;

            } catch (Exception $exception) {
                $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED);
                $this->progressBar->finish();

                return false;
            }
        }

        $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_SKIP_DOWNLOAD);
        $this->progressBar->finish();

        return true;
    }

    /**
     * @param $photoId
     * @return boolean|mixed
     * @throws ConnectionException
     */
    private function getPhoto($photoId)
    {
        try {
            if ($photoId) {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE `params` IS NOT NULL AND id = ? LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NOT NULL LIMIT 1 FOR UPDATE';
            }

            return $this->connection->executeQuery($query, [$photoId])->fetch(FetchMode::STANDARD_OBJECT);
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            return false;
        }
    }

    /**
     * @param $media
     * @param $photoId
     * @return boolean
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function verifyMediaType($media, $photoId)
    {
        // At the moment we only download photos
        if ($media !== 'photo') {
            $this->logWarning('It\'s not media \'photo\': '.$media);
            $this->updatePhotoStatus($photoId, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_PHOTO);
            $this->progressBar->finish();

            return false;
        }

        return true;
    }

    /**
     * @param $photoId
     * @param $status
     * @return bool
     * @throws ConnectionException
     * @throws DBALException
     */
    private function updatePhotoStatus($photoId, $status)
    {
        $this->info('State updated: '.$status);

        try {

            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_photos` SET status = ? WHERE id = ?',
                array($status, $photoId)
            );
            $this->connection->commit();
            $this->connection->close();

            return true;
        } catch (Exception $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            return false;
        }
    }
}