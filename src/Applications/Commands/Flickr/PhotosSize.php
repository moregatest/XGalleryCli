<?php

namespace XGallery\Applications\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends CommandFlickr
{
    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->description = 'Fetch photos size';
        $this->options = [
            'nsid' => [],
        ];

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 2);
        $nsid = $input->getOption('nsid');

        if ($nsid) {
            $output->writeln('Getting '.DefinesFlickr::REST_LIMIT_PHOTOS_SIZE.' photos from NSID: '.$nsid.' ...');
        } else {
            $output->writeln('Getting '.DefinesFlickr::REST_LIMIT_PHOTOS_SIZE.' photos ...');
        }

        $photos = $this->getPhotos($nsid);

        if (!$photos || empty($photos)) {
            $output->writeln('There are no photos');

            return false;
        }

        $progressBar->advance();

        $output->writeln("\nFound ".count($photos)." photos");
        $output->writeln("Fetching size ...");

        $connection = Factory::getDbo();

        foreach ($photos as $photoId) {
            $output->writeln('Fetching: '.$photoId);
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);

            if (!$photoSize) {
                $output->writeln('Something wrong on photo_id: '.$photoId);
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

        $progressBar->finish();
        $this->complete($output);
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

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NULL';

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