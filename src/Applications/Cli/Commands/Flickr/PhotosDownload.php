<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

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
     * @var array
     */
    private $photos;

    /**
     * @var int
     */
    private $totalPhotos = 0;

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
            'nsid' =>
                [
                    'description' => 'Download specific NSID',
                    'default' => null,
                ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->loadPhotos()) {
            return false;
        }

        return true;
    }


    /**
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(
            [
                'downloadPhotos',
            ]
        );
    }

    /**
     * @return boolean
     */
    protected function loadPhotos()
    {
        $this->info(__FUNCTION__);

        try {
            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE `status` = 0 AND `params` IS NOT NULL ';

            $nsid = $this->input->getOption('nsid');

            if ($nsid) {
                $this->info('Specific on NSID: '.$nsid);
                $query .= ' AND `owner` = ?';
            }

            $query .= ' LIMIT '.(int)$this->input->getOption('limit');

            if ($nsid) {
                $this->photos = $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);
            } else {
                $this->photos = $this->connection->executeQuery($query, [])->fetchAll(FetchMode::COLUMN);
            }


            if (!$this->photos || empty($this->photos)) {
                $this->logNotice('There are no photos');

                return false;
            }

            $this->totalPhotos = count($this->photos);
            $this->info('Total photos being download: '.$this->totalPhotos, [], true);

            return true;
        } catch (DBALException $exception) {
            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function downloadPhotos()
    {
        $processes = [];

        foreach ($this->photos as $photoId) {
            $this->info('Sending request: '.$photoId, [], true);
            try {
                $processes[$photoId] = new Process(['php', 'cli.php', 'flickr:photodownload', '--photo_id='.$photoId]);
                $processes[$photoId]->start();
                $this->progressBar->advance();
            } catch (Exception $exception) {
                $this->logError($exception->getMessage());
            }
        }

        foreach ($processes as $id => $process) {
            $this->info('Downloading '.$id.' ...');
            $process->wait();
            $this->logInfo('Process complete: '.$id);
        }

        return true;
    }
}