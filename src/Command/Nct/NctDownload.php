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

use App\Service\HttpClient;
use SplFileInfo;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Command\NctCommand;

/**
 * Class NctDownload
 * @package App\Command\Nct
 */
final class NctDownload extends NctCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Download song')
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
     */
    protected function processDownload()
    {
        $url = $this->getOption('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->logNotice('URL is not provided or invalid URL');

            return false;
        }

        $this->log('Download file: ' . $url);
        $download = $this->client->extractItem($url);

        if (!$download) {
            $this->logError('Can not extract item');

            return false;
        }

        $info   = new SplFileInfo($download['download']);
        $parts  = explode('?', $info->getBasename());
        $saveTo = getenv('nct_storage') . '/' . $download['creator'];

        if (!file_exists($saveTo) || !is_dir($saveTo)) {
            (new Filesystem())->mkdir($saveTo);
        }

        if (!HttpClient::download($download['download'], $saveTo . '/' . $parts[0])) {
            return false;
        }

        return true;
    }
}
