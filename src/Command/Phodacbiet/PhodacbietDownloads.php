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

use App\Traits\HasStorage;
use GuzzleHttp\Exception\GuzzleException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Command\PhodacbietCommand;

/**
 * Class PhodacbietDownloads
 * @package App\Command\Phodacbiet
 */
class PhodacbietDownloads extends PhodacbietCommand
{
    use HasStorage;

    /**
     * @throws GuzzleException
     */
    public function processDownloads()
    {
        $this->io->newLine();
        $this->io->progressStart(7);

        $threads = [];

        for ($index = 1; $index <= 7; $index++) {
            $url     = 'https://phodacbiet.info/forums/anh-hotgirl-nguoi-mau.43/page-' . $index;
            $threads = array_merge($threads, $this->client->getThreads($url));
            $this->io->progressAdvance();
        }

        $this->io->progressStart(count($threads));

        foreach ($threads as $thread) {
            $images = $this->client->getThreadImages($thread);

            foreach ($images as $image) {
                $info     = new SplFileInfo($image);
                $baseName = $info->getBasename();
                $baseName = explode('.', $baseName);
                $baseName = $baseName[0];

                $parts    = explode('-', $baseName);
                $fileName = $baseName . '.' . end($parts);

                $saveDir = $this->getStorage('phodacbiet') . '/' . md5($thread);
                (new Filesystem())->mkdir($saveDir);

                $this->client->download(
                    $image,
                    $saveDir . '/' . $fileName
                );
            }

            $this->io->progressAdvance();
        }
    }
}
