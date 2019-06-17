<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Batdongsan;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\Command\BatdongsanCommand;

/**
 * Class BatdongsanFetch
 * @package App\Command\Batdongsan
 */
final class BatdongsanFetch extends BatdongsanCommand
{
    /**
     * @var integer
     */
    private $pages;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch BDS URLs');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputOption(
                        'url',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Index URL',
                        'https://batdongsan.com.vn/nha-dat-ban'
                    ),
                ]
            )
        );

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function prepareGetPages()
    {
        $this->pages = $this->client->getPages($this->getOption('url'));
        $this->log('Total pages: <options=bold>' . $this->pages . '</>');

        return self::PREPARE_SUCCEED;
    }

    /**
     * Process insert items for all pages
     */
    protected function processInsertItems()
    {
        $this->io->newLine();
        $this->io->progressStart($this->pages);

        /**
         * Process extract items & import for each page
         * @TODO Support multi pages at same time
         */
        for ($page = 1; $page <= $this->pages; $page++) {
            $this->getProcess(['batdongsan:import', '--url=' . $this->getOption('url') . '/p' . $page])->run();

            $this->io->progressAdvance();
        }

        return true;
    }
}
