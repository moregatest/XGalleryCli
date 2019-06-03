<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Nct;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\NctCommand;

/**
 * Class NctDownload
 * @package App\Command\Nct
 */
class NctDownload extends NctCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('nct:download')
            ->setDescription('Download song')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('url', null, InputOption::VALUE_OPTIONAL),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processDownload()
    {
        $url = $this->getOption('url');

        if (!$url) {
            return false;
        }

        $download = $this->client->getSong($url);

        if (!$download) {
            return false;
        }

        // Process download
    }
}
