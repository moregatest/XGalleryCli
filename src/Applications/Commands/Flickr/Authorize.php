<?php

namespace XGallery\Applications\Commands\Flickr;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;

/**
 * Class Authorize
 * @package XGallery\Applications\Commands\Flickr
 */
class Authorize extends CommandFlickr
{
    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->description = 'Fetch OAuth authorization';
        $this->options = [
            'step' => [],
        ];

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($input->getOption('step')) {
            case 1:
                $output->writeln($this->flickr->getRequestToken('http://localhost'));
                break;
            case 2:
                $output->writeln(
                    $this->flickr->getAccessToken('', '')
                );
                break;
        }
    }
}