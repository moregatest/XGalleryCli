<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosDownload
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosDownload extends AbstractCommandFlickr
{

    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Mass download all photos');
        $this->options = [
            'limit' =>
                [
                    'description' => 'Limit number of download',
                    'default' => DefinesFlickr::DOWNLOAD_LIMIT,
                ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function process()
    {
        try {
            $connection = Factory::getDbo();
            $connection->beginTransaction();

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL LIMIT '
                .(int)$this->input->getOption('limit');
            $photos = $connection->executeQuery($query, [])->fetchAll(
                FetchMode::COLUMN
            );

            if (!$photos) {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            $connection->commit();
            $connection->close();

            $this->info('Total photos being download: '.count($photos));
            $this->output->writeln('');

            foreach ($photos as $photoId) {
                $this->output->writeln('Request download: '.$photoId);
                $process = new Process(['php', 'cli.php', 'flickr:photodownload', '--photo_id='.$photoId]);
                $process->start();
            }

            return true;
        } catch (Exception $exception) {
            $connection->rollBack();
            $connection->close();

            return false;
        }
    }
}