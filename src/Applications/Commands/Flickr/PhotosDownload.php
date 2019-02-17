<?php

namespace XGallery\Applications\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class PhotosDownload
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosDownload extends CommandFlickr
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $connection = Factory::getDbo();
            $connection->beginTransaction();

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL';
            $photos = $connection->executeQuery($query)->fetchAll(FetchMode::COLUMN);

            if (!$photos) {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            $connection->commit();
            $connection->close();

            $output->writeln('Total photos being download: '.count($photos));

            foreach ($photos as $photoId) {
                $output->writeln('Request download: '.$photoId);
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