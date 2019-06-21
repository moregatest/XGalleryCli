<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Media;

use App\Entity\JavMedia;
use Exception;
use FFMpeg\FFProbe;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use XGallery\AbstractCommand;
use XGallery\Command\MediaCommand;

/**
 * Class MediaFiles
 * @package App\Command\Media
 */
final class MediaFiles extends AbstractCommand
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Scan local media files');

        parent::configure();
    }

    /**
     * @param string $name
     */
    protected function getClient($name = '')
    {
        return;
    }

    /**
     * @return boolean
     */
    protected function prepareGetFiles()
    {
        $this->finder = new Finder;
        $this->finder->files()->in('/mnt/e')->ignoreUnreadableDirs()->ignoreDotFiles(true)->filter(
            function (SplFileInfo $file) {
                // Skip
                if (in_array($file->getExtension(), ['iso', 'jpg'])) {
                    return self::PREPARE_FAILED;
                }

                // Delete
                if (in_array($file->getExtension(), ['ini', 'txt', 'torrent', 'url', 'mht', 'gif', 'chm'])) {
                    (new Filesystem())->remove($file->getRealPath());

                    return self::PREPARE_FAILED;
                }
            }
        );

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    protected function processFiles()
    {
        $this->io->newLine();
        $this->io->progressStart($this->finder->count());

        foreach ($this->finder as $index => $file) {
            $ext = strtolower($file->getExtension());

            if (!in_array($ext, ['mp4', 'mkv', 'avi'])) {
                continue;
            }

            $movieEntity = $this->entityManager->getRepository(JavMedia::class)->findOneBy(
                ['filename' => $file->getFilename()]
            );

            if (!$movieEntity) {
                $movieEntity = new JavMedia;
            }

            $newFileName = str_replace(
                ['[HD]', '(HD)', '[FHD]', '(CEN)', '[Thz.la]'],
                '',
                $file->getFilenameWithoutExtension()
                ) . '.' . $ext;
            $newFilePath = $file->getPath() . DIRECTORY_SEPARATOR . $newFileName;

            if (!file_exists($newFilePath)) {
                // Rename file with correct format
                (new Filesystem)->rename($file->getRealPath(), $newFilePath);
            }

            chmod($newFilePath, 0755);

            try {
                $media = FFProbe::create()->streams($newFilePath)
                    ->videos()
                    ->first()
                    ->all();
            } catch (Exception $exception) {
                continue;
            }

            $movieEntity->setFilename($newFileName);
            $movieEntity->setDirectory($newFilePath);
            $movieEntity->setCodecName($media['codec_name'] ?? null);
            $movieEntity->setCodecLongName($media['codec_long_name'] ?? null);
            $movieEntity->setWidth($media['width'] ?? null);
            $movieEntity->setHeight($media['height'] ?? null);
            $movieEntity->setDuration($media['duration'] ?? null);
            $movieEntity->setBitRate($media['bit_rate'] ?? null);
            $movieEntity->setBitsPerRawSample($media['bits_per_raw_sample'] ?? null);
            $movieEntity->setNbFrames($media['nb_frames'] ?? null);
            $movieEntity->setFileSize(filesize($newFilePath));

            $this->entityManager->persist($movieEntity);
            $this->entityManager->flush();

            $this->io->progressAdvance();
        }

        return true;
    }
}
