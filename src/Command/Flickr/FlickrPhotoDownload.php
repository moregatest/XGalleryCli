<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use App\Entity\FlickrPhoto;
use App\Service\HttpClient;
use Exception;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Command\FlickrCommand;

/**
 * Class FlickrPhotoDownload
 * @package App\Command\Flickr
 */
final class FlickrPhotoDownload extends FlickrCommand
{
    /**
     * @var FlickrPhoto
     */
    private $photo;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download photo')
            ->setDefinition(
                new InputDefinition(
                    [
                        /**
                         * @TODO Support photo ID via URL
                         */
                        new InputOption(
                            'photo_id',
                            'pid',
                            InputOption::VALUE_REQUIRED,
                            'Download specific photo by ID'
                        ),
                        new InputOption(
                            're_download',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific album URL'
                        ),
                        new InputOption(
                            'no_download',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific gallery URL'
                        ),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * Get photo & sizes for download from database
     *
     * @return boolean
     */
    protected function prepareGetPhotoFromDatabase()
    {
        static $retry = false;

        $photoId = $this->getOption('photo_id');

        if (!$photoId) {
            return self::NEXT_PREPARE;
        }

        $this->photo = $this->entityManager->getRepository(FlickrPhoto::class)->find($photoId);

        // Photo not found in database with specific id
        if (!$this->photo) {
            $this->log('Photo not found in database', 'notice');

            return self::NEXT_PREPARE;
        }

        $this->log('Found photo in database: ' . $this->photo->getId());

        if ($this->photo->getUrl() === null && $retry === true) {
            $this->log('Retried but not succeed', 'notice');

            return self::PREPARE_FAILED;
        }

        // Get photo size if needed
        if ($this->photo->getUrl() === null) {
            $this->log('Trying get photo size');
            $retry = true;

            $this->getProcess(['flickr:photossize', '--photo_ids=' . $this->photo->getId()])->run();

            $this->entityManager->clear();

            // Try to check this photo again
            return $this->prepareGetPhotoFromDatabase();
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Fetch photo online
     *
     * @return boolean|integer
     */
    protected function prepareGetPhotoOnline()
    {
        $photoId = $this->getOption('photo_id');

        if (!$photoId) {
            return self::PREPARE_FAILED;
        }

        $this->log('Requesting photo size: <options=bold>' . $photoId . '</>...');

        $this->getProcess(['flickr:photossize', '--photo_ids=' . $photoId])->run();

        // Try to get it again
        $this->entityManager->clear();
        $this->photo = $this->entityManager->getRepository(FlickrPhoto::class)->find($photoId);

        if (!$this->photo) {
            $this->log('Can not get photo', 'notice');

            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Download process
     *
     * @return boolean
     * @throws Exception
     */
    protected function processDownload()
    {
        // Prepare
        $targetDir = getenv('flickr_storage') . '/' . $this->photo->getOwner();
        $fileName  = basename($this->photo->getUrl());
        $fileName  = explode('?', $fileName);
        $fileName  = $fileName[0];
        $saveTo    = $targetDir . '/' . $fileName;

        $this->log('URL: ' . $this->photo->getUrl());
        $this->log('Save file to: ' . $saveTo);

        /**
         * @TODO Check if can access
         */

        $fileSystem = new Filesystem;
        $fileSystem->mkdir($targetDir);

        $fileExists = $fileSystem->exists($saveTo);

        // File exists
        if ($fileExists) {
            $this->log('Photo already exists: ' . $saveTo, 'notice');

            if ($this->photo->getUrl() === null || empty($this->photo->getUrl())) {
                $this->getProcess(
                    [
                        'flickr:photossize',
                        '--photo_ids=' . $this->photo->getId(),
                    ]
                )->run();

                $this->photo = $this->entityManager->getRepository(FlickrPhoto::class)->find($this->photo->getId());
            }

            // Verify load and re-download if file is corrupted
            $originalFilesize = filesize($saveTo);
            $remoteFilesize   = HttpClient::getFilesize($this->photo->getUrl());

            // Than we only re-download if corrupted and re-download is required
            if ($originalFilesize !== $remoteFilesize) {
                $this->log('Local file-size: ' . $originalFilesize . ' vs remote file-size: ' . $remoteFilesize);

                if ($this->getOption('re_download') != 1) {
                    $this->photo->setStatus(self::PHOTO_STATUS_LOCAL_CORRUPTED);
                    $this->entityManager->persist($this->photo);
                    $this->entityManager->flush();

                    return false;
                }

                $this->log('Local file is corrupted: ' . $saveTo . '. Re-downloading ...', 'notice');

                if (!HttpClient::download($this->photo->getUrl(), $saveTo)) {
                    $this->photo->setStatus(self::PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED);
                    $this->entityManager->persist($this->photo);
                    $this->entityManager->flush();

                    return false;
                }

                $this->photo->setStatus(self::PHOTO_STATUS_DOWNLOADED);
                $this->entityManager->persist($this->photo);
                $this->entityManager->flush();

                return true;
            }

            $this->photo->setStatus(self::PHOTO_STATUS_ALREADY_DOWNLOADED);
            $this->entityManager->persist($this->photo);
            $this->entityManager->flush();

            return true;
        }

        if ($this->getOption('no_download') == 1) {
            $this->log('Skip download', 'notice');

            $this->photo->setStatus(self::PHOTO_STATUS_SKIP_DOWNLOAD);
            $this->entityManager->persist($this->photo);
            $this->entityManager->flush();

            return true;
        }

        if (!HttpClient::download($this->photo->getUrl(), $saveTo)) {
            $this->photo->setStatus(self::PHOTO_STATUS_ERROR_DOWNLOAD_FAILED);
            $this->entityManager->persist($this->photo);
            $this->entityManager->flush();

            return false;
        }

        $this->log('Download completed: ' . $targetDir . '/' . $fileName);

        $this->photo->setStatus(self::PHOTO_STATUS_DOWNLOADED);
        $this->entityManager->persist($this->photo);
        $this->entityManager->flush();

        return true;
    }
}
