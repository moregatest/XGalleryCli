<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Pornhub;

use App\Traits\HasStorage;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\CrawlerCommand;

/**
 * Class PornhubDownload
 * @package App\Command\Pornhub
 */
class PornhubDownload extends CrawlerCommand
{
    use HasStorage;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Pornhub download')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('url', null, InputOption::VALUE_OPTIONAL, 'URL'),
                    ]
                )
            );

        parent::configure();
    }

    protected function processDownload()
    {
        if (!$data = $this->getClient()->getDetail($this->getOption('url'))) {
            return false;
        }

        $medias = $data->mediaDefinitions;

        foreach ($medias as $media) {
            /**
             * @TODO Try to download 1080
             */
            if ((int)$media->quality !== 720) {
                continue;
            }

            $this->log('Download ' . $media->videoUrl);
        }

        return true;
    }
}
