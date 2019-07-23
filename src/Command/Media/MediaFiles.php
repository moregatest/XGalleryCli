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
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use XGallery\BaseCommand;

/**
 * Class MediaFiles
 * @package App\Command\Media
 */
final class MediaFiles extends BaseCommand
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
        $this->setDescription('Scan local media files')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption(
                            'paths',
                            'p',
                            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                            'Specific path',
                            ['/mnt/e', '/mnt/j/JAV']
                        ),
                        new InputOption(
                            'skip',
                            's',
                            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                            'Skip file extensions',
                            ['iso', 'jpg']
                        ),
                        new InputOption(
                            'delete',
                            '',
                            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                            'Delete file extensions',
                            ['ini', 'txt', 'torrent', 'url', 'mht', 'gif', 'chm']
                        ),
                        new InputOption(
                            'replace',
                            '',
                            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                            'Replace text',
                            [
                                '[HD]',
                                '(HD)',
                                '.HD',
                                '[FHD]',
                                'FHD',
                                '(CEN)',
                                '[Thz.la]',
                                '【Thz.la】',
                                '[ThZu.Cc]',
                                'g-cup.tv',
                                '.1080p',
                                '(ORE)',
                                '-h264',
                                'hjd2048.com-',
                                'www.av9.cc-',
                                '[44x.me]',
                            ]
                        ),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function processLocalFiles()
    {
        $this->finder = new Finder;
        $paths        = $this->getOption('paths');

        foreach ($paths as $path) {
            $this->finder->files()->in($path)->ignoreUnreadableDirs()->ignoreDotFiles(true)->filter(
                function (SplFileInfo $file) {
                    // Skip trash bin
                    if (strpos($file->getPath(), '$RECYCLE.BIN') !== false) {
                        return false;
                    }

                    // Skip extensions
                    if (in_array($file->getExtension(), $this->getOption('skip'))) {
                        return false;
                    }

                    // Delete junk files
                    if (in_array($file->getExtension(), $this->getOption('delete'))) {
                        (new Filesystem())->remove($file->getRealPath());

                        return false;
                    }
                }
            );

            $this->io->newLine();
            $this->io->progressStart($this->finder->count());

            foreach ($this->finder as $index => $file) {
                $this->updateFile($file);
                $this->io->progressAdvance();
            }
        }

        return true;
    }

    /**
     * @param \SplFileInfo $file
     * @return bool
     */
    private function updateFile($file)
    {
        $ext = strtolower($file->getExtension());

        if (!in_array($ext, ['mp4', 'mkv', 'avi'])) {
            return false;
        }

        if (!$movieEntity = $this->entityManager->getRepository(JavMedia::class)
            ->findOneBy(['filename' => $file->getFilename()])) {
            $movieEntity = new JavMedia;
        }

        $dirPath          = $file->getPath();
        $originalFilepath = $dirPath . DIRECTORY_SEPARATOR . $movieEntity->getFilename();

        // File not found but record found. Then delete record
        if (!\App\Utils\Filesystem::exists($originalFilepath) && $movieEntity->getId()) {
            // Delete record
            $this->entityManager->remove($movieEntity);
            $this->entityManager->flush();

            return true;
        }

        // Rename file name
        $newFileName = str_replace(
                $this->getOption('replace'),
                '',
                pathinfo($file->getFilename(), PATHINFO_FILENAME)
            ) . '.' . $ext;
        $newFilePath = $dirPath . DIRECTORY_SEPARATOR . $newFileName;

        if ($file->getRealPath() !== $newFilePath && !file_exists($newFilePath)) {
            // Rename file with correct format
            \App\Utils\Filesystem::rename($file->getRealPath(), $newFilePath);
        }

        try {
            $media = FFProbe::create()->streams($newFilePath)
                ->videos()
                ->first()
                ->all();
        } catch (Exception $exception) {
            $this->io->progressAdvance();

            return false;
        }

        $movieEntity->setFilename($newFileName);
        $movieEntity->setDirectory($dirPath);
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

        return true;
    }

    /**
     * @return boolean
     */
    protected function processDatabaseFiles()
    {
        $entities = $this->entityManager->getRepository(JavMedia::class)->findAll();

        if (!$entities) {
            return false;
        }

        $this->io->newLine();
        $this->io->progressStart(count($entities));

        foreach ($entities as $entity) {
            $file = new \SplFileInfo($entity->getDirectory() . DIRECTORY_SEPARATOR . $entity->getFilename());
            $this->updateFile($file);
            $this->io->progressAdvance();
        }

        return true;
    }
}
