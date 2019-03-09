<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotoDownload extends AbstractCommandFlickr
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @throws \ReflectionException
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
                'default' => 1,
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function process()
    {
        if ($photoId = $this->input->getOption('photo_id')) {
            $this->info('Getting photo ID: '.$photoId.' ...');
        } else {
            $this->info('Getting photo ...');
        }


        $this->connection = Factory::getDbo();
        $this->connection->beginTransaction();

        $photo = $this->getPhoto($photoId);

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

        // At the moment we only download photos
        if ($lastSize->media !== 'photo') {
            $this->logWarning('It\'s not media \'photo\': '.$lastSize->media);

            $this->updatePhotoStatus($photo->id, DefinesFlickr::PHOTO_STATUS_ERROR_NOT_PHOTO);

            return false;
        }

        $this->info('Photo URL: '.$lastSize->source);

        $targetDir = getenv('flickr_storage').'/'.$photo->owner;
        $fileName = basename($lastSize->source);

        $fileExists = (new Filesystem())->exists($targetDir.'/'.$fileName);

        if ($fileExists && $this->input->getOption('force') == 0) {
            $this->logWarning('Photo already exists: '.$targetDir.'/'.$fileName);
            $this->info('Photo already exists: '.$targetDir.'/'.$fileName);

            $status = DefinesFlickr::PHOTO_STATUS_ALREADY_DOWNLOADED;
        } else {
            // Skip download
            if ($this->input->getOption('no_download') == 0) {
                $this->info('Downloading photo ...');

                try {

                    $saveTo = $targetDir.'/'.$fileName;

                    if (!$this->download($lastSize->source, $saveTo)) {

                        $this->updatePhotoStatus(
                            $photo->id,
                            DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED
                        );


                        return false;
                    }

                    $status = DefinesFlickr::PHOTO_STATUS_DOWNLOADED;

                    $this->info('Download completed: '.$targetDir.'/'.$fileName);

                } catch (Exception $exception) {
                    $status = DefinesFlickr::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED;
                }
            } else {
                $status = DefinesFlickr::PHOTO_STATUS_SKIP_DOWNLOAD;
            }
        }

        $this->updatePhotoStatus($photo->id, $status);

        return true;
    }

    /**
     * @param $photoId
     * @return boolean|mixed
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function getPhoto($photoId)
    {
        try {
            if ($photoId) {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE `params` IS NOT NULL AND id = ? LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NOT NULL LIMIT 1 FOR UPDATE';
            }

            $stmt = $this->connection->executeQuery($query, [$photoId]);

            return $stmt->fetch(FetchMode::STANDARD_OBJECT);
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            return false;
        }
    }

    /**
     * @param $url
     * @param $saveTo
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function download($url, $saveTo)
    {
        $client = new Client();
        $response = $client->request('GET', $url, ['sink' => $saveTo]);
        $orgFileSize = $response->getHeader('Content-Length')[0];
        $downloadedFileSize = filesize($saveTo);

        if ($orgFileSize != $downloadedFileSize) {
            $this->logWarning('Filesize does not match');

            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return true;
    }

    /**
     * @param $photoId
     * @param $status
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
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