<?php

namespace XGallery\Applications\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotoDownload extends CommandFlickr
{

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->description = 'Download photo';
        $this->options = [
            'photo_id' => [],
        ];

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $progressBar = new ProgressBar($output, 2);

            $photoId = $input->getOption('photo_id');

            if ($photoId) {
                $output->writeln('Getting photo ID: '.$photoId.' ...');
            } else {
                $output->writeln('Getting photo ...');
            }

            $connection = Factory::getDbo();
            $connection->beginTransaction();

            if ($photoId) {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL AND id = ? LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL LIMIT 1 FOR UPDATE';
            }

            $stmt = $connection->executeQuery($query, [$photoId]);
            $photo = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            // There are no photos
            if (!$photo) {
                $connection->close();

                return false;
            }

            $progressBar->advance();

            $output->writeln("\nWork on photo: ".$photo->id);

            $status = $photo->status;

            $size = json_decode($photo->params);
            $lastSize = end($size);

            if (!$lastSize) {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            if ($lastSize->media !== 'photo') {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            $output->writeln('Photo URL: '.$lastSize->source);

            $targetDir = '/mnt/g/XGalleryCli/storage/flickr/'.$photo->owner;
            $fileName = basename($lastSize->source);

            $fileExists = (new Filesystem())->exists($targetDir.'/'.$fileName);

            if ($fileExists) {
                $output->writeln('Photo already exists: '.$targetDir.'/'.$fileName);
                $status = DefinesFlickr::PHOTO_STATUS_DOWNLOADED;
            } else {
                $output->writeln('Downloading photo ...');
                $client = new Client();

                try {
                    $response = $client->request('GET', $lastSize->source, ['sink' => $targetDir.'/'.$fileName]);
                    $status = DefinesFlickr::PHOTO_STATUS_DOWNLOADED;

                    if ($response->getStatusCode() !== 200) {
                        $status = -1;
                    }

                } catch (Exception $exception) {
                    $status = -1;
                }
            }

            $connection->executeUpdate(
                'UPDATE `xgallery_flickr_photos` SET status = ? WHERE id = ?',
                array($status, $photo->id)
            );

            $connection->commit();
            $connection->close();


            $progressBar->finish();
            $this->complete($output);

        } catch (Exception $exception) {
            $connection->rollBack();
            $connection->close();
        }
    }
}