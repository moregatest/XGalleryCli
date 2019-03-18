<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;

/**
 * Class Authorize
 * @package XGallery\Applications\Commands\Flickr
 */
class Authorize extends AbstractCommandFlickr
{
    /**
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
     * @param array $steps
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function process($steps = [])
    {
        switch ($this->input->getOption('step')) {
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