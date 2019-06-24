<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Xiuren;

use XGallery\CrawlerCommand;

/**
 * Class XiurenDownloads
 * @package App\Command\Xiuren
 */
final class XiurenDownloads extends CrawlerCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download ALL images from xiuren.org');

        parent::configure();
    }

    /**
     * @return boolean
     */
    protected function processDownloads()
    {
        $this->getClient()->getAllDetailLinks(
            function ($pages) {
                $this->io->newLine();
                $this->io->progressStart($pages);
            },
            function ($links) {
                if (!$links || empty($links)) {
                    return;
                }

                $processes = [];

                foreach ($links as $index => $link) {
                    $processes[$index] = $this->getProcess(['xiuren:download', '--url=' . $link]);
                    $processes[$index]->start();
                }

                foreach ($processes as $process) {
                    $process->wait();
                }

                $this->io->progressAdvance();
            }
        );

        return true;
    }
}
