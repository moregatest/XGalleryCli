<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ReflectionException;
use Symfony\Component\Process\Process;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;
use XGallery\Exceptions\Exception;

/**
 * Class PhotosDownload
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosDownload extends AbstractCommandFlickr
{

    /**
     * @throws ReflectionException
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
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function process()
    {
        try {

            $this->connection->beginTransaction();

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL LIMIT '
                .(int)$this->input->getOption('limit');
            $photos = $this->connection->executeQuery($query, [])->fetchAll(
                FetchMode::COLUMN
            );

            if (!$photos) {
                $this->connection->rollBack();
                $this->connection->close();

                return false;
            }

            $this->connection->commit();
            $this->connection->close();

            $this->info('Total photos being download: '.count($photos), [], true);

            $this->progressBar->setMaxSteps(count($photos));

            foreach ($photos as $photoId) {
                $this->progressBar->advance();
                $process = new Process(['php', 'cli.php', 'flickr:photodownload', '--photo_id='.$photoId]);
                $process->start();
            }

            $this->progressBar->finish();

            return true;
        } catch (Exception $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            return false;
        }
    }
}