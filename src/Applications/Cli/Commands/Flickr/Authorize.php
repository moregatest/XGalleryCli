<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Applications\Cli\Commands\CommandFlickr;

/**
 * Class Authorize
 * @package XGallery\Applications\Commands\Flickr
 */
class Authorize extends AbstractCommandFlickr
{
    /**
     * @throws \ReflectionException
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
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function process()
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