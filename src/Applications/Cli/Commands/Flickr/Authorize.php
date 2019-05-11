<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use GuzzleHttp\Exception\GuzzleException;
use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;

/**
 * Class Authorize
 * @package XGallery\Applications\Commands\Flickr
 */
final class Authorize extends AbstractCommandFlickr
{
    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Get OAuth authorization');
        $this->options = [
            'step' => [
                'default' => 1,
                'description' => 'Step 1 to get RequestToken. Step 2 to get AccessToken',
            ],
        ];

        parent::configure();
    }

    /**
     * process
     *
     * @return boolean
     * @throws GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function process()
    {
        switch ($this->getOption('step')) {
            case 1:
                $this->output->writeln($this->flickr->getRequestToken('http://localhost'));
                break;
            case 2:
                $this->output->writeln($this->flickr->getAccessToken('', ''));
                break;
        }

        return true;
    }
}
