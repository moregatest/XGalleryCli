<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends AbstractCommandFlickr
{
    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch photos size');
        $this->options = [
            'nsid' => [
                'description' => 'Only fetch photos from this NSID',
            ],
            'limit' => [
                'default' => DefinesFlickr::REST_LIMIT_PHOTOS_SIZE,
                'description' => 'Number of photos will be used for get sizes',
            ],
        ];

        parent::configure();
    }

    /**
     * @return bool
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function process()
    {
        $this->info('Fetching sizes ...', [], true);
        $this->progressBar->start(2);

        if ($nsid = $this->input->getOption('nsid')) {
            $this->info('Getting '.$this->input->getOption('limit').' photos from NSID: '.$nsid.' ...');
        }

        $photos = $this->getPhotos($nsid);

        $this->progressBar->advance();

        if (!$photos || empty($photos)) {
            $this->logWarning('There are no photos');
            $this->progressBar->finish();

            return false;
        }

        $this->info('Found '.count($photos).' photos');
        $this->info("Fetching size ...");

        foreach ($photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->info('Fetched '.$photoId);

            if (!$photoSize) {
                $this->logNotice('Something wrong on photo_id: '.$photoId);

                continue;
            }

            try {
                $this->connection->beginTransaction();
                $this->connection->executeUpdate(
                    'UPDATE `xgallery_flickr_photos` SET `params` = ? WHERE `id` = ?',
                    [json_encode($photoSize->sizes->size), $photoId]
                );
                $this->connection->commit();
                $this->connection->close();
            } catch (DBALException $exception) {
                $this->connection->rollBack();
                $this->connection->close();
            }
        }

        $this->output->write("\n");
        $this->progressBar->finish();

        return true;
    }

    /**
     * @param $nsid
     * @return boolean|mixed[]
     * @throws ConnectionException
     * @throws DBALException
     */
    private function getPhotos($nsid = '')
    {
        try {

            $this->connection->beginTransaction();

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL ';

            if ($nsid) {
                $query .= ' AND owner = ?';
            }

            $query .= 'LIMIT '.(int)$this->input->getOption('limit').' FOR UPDATE';

            $photos = $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);

            if (!$photos) {
                $this->connection->rollBack();
                $this->connection->close();

                return false;
            }

            $this->connection->commit();
            $this->connection->close();

            return $photos;
        } catch (DBALException $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            return false;
        }
    }
}