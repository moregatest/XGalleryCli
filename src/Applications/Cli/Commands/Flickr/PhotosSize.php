<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends AbstractCommandFlickr
{
    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch photos size');
        $this->options = [
            'nsid' => [
                'description',
                'Only fetch photos from this NSID',
            ],
        ];

        parent::configure();
    }

    /**
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function process()
    {
        $this->info('Fetching sizes ...');

        if ($nsid = $this->input->getOption('nsid')) {
            $this->info('Getting '.DefinesFlickr::REST_LIMIT_PHOTOS_SIZE.' photos from NSID: '.$nsid.' ...');
        } else {
            $this->info('Getting '.DefinesFlickr::REST_LIMIT_PHOTOS_SIZE.' photos ...');
        }

        $photos = $this->getPhotos($nsid);

        if (!$photos || empty($photos)) {
            $this->logWarning('There are no photos');

            return false;
        }

        $this->info('Found '.count($photos).' photos');
        $this->info("Fetching size ...");

        $connection = Factory::getDbo();

        foreach ($photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->info('Fetched '.$photoId);

            if (!$photoSize) {
                $this->logNotice('Something wrong on photo_id: '.$photoId);

                continue;
            }

            try {
                $connection->beginTransaction();
                $connection->executeUpdate(
                    'UPDATE `xgallery_flickr_photos` SET `params` = ? WHERE `id` = ?',
                    [json_encode($photoSize->sizes->size), $photoId]
                );
                $connection->commit();
                $connection->close();

            } catch (Exception $exception) {
                $connection->rollBack();
                $connection->close();
            }
        }

        return true;
    }

    /**
     * @param $nsid
     * @return bool|mixed[]
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getPhotos($nsid = '')
    {
        try {
            $connection = Factory::getDbo();
            $connection->beginTransaction();

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL ';

            if ($nsid) {
                $query .= ' AND owner = ?';
            }

            $query .= 'LIMIT '.DefinesFlickr::REST_LIMIT_PHOTOS_SIZE.' FOR UPDATE';

            $photos = $connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);

            if (!$photos) {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            $connection->commit();
            $connection->close();

            return $photos;
        } catch (Exception $exception) {
            $connection->rollBack();
            $connection->close();

            return false;
        }
    }
}