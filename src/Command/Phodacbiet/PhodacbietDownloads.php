<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Phodacbiet;

use App\Service\Crawler\PhodacbietCrawler;
use App\Traits\HasStorage;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\CrawlerCommand;

/**
 * Class PhodacbietDownloads
 * @package App\Command\Phodacbiet
 */
final class PhodacbietDownloads extends CrawlerCommand
{
    use HasStorage;

    /**
     * @var PhodacbietCrawler
     */
    private $client;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download Phodacbiet photos');

        parent::configure();
    }

    protected function processDownloads()
    {
        $this->client = $this->getClient();
        $this->client->getAllDetailLinks(
            function ($pages) {
                $this->io->newLine();
                $this->io->progressStart($pages);
            },
            function ($links) {
                foreach ($links as $link) {
                    $images = $this->client->getDetail($link);

                    if (!$images || empty($images)) {
                        continue;
                    }

                    $saveDir = $this->getStorage('phodacbiet') . '/' . md5($link);
                    (new Filesystem())->mkdir($saveDir);

                    foreach ($images as $image) {
                        $info     = new SplFileInfo($image);
                        $baseName = $info->getBasename();
                        $baseName = explode('.', $baseName);
                        $baseName = $baseName[0];

                        $parts    = explode('-', $baseName);
                        $fileName = $baseName . '.' . end($parts);

                        /**
                         * @TODO Use wget instead
                         */
                        $this->client->download($image, $saveDir . '/' . $fileName);
                    }
                }

                $this->io->progressAdvance();
            }
        );

        return true;
    }
}
