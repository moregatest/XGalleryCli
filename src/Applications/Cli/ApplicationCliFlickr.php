<?php

namespace XGallery\Applications\Cli;

use Symfony\Component\Finder\Finder;
use XGallery\Applications\AbstractApplicationCli;

/**
 * Class ApplicationFlickr
 *
 * @package XGallery\Applications\Commands
 */
class ApplicationCliFlickr extends AbstractApplicationCli
{
    /**
     *
     */
    protected function registerCommands()
    {
        $files = (new Finder())->files()->in(__DIR__.'/Commands/Flickr')->depth(0)->name('*.php');

        foreach ($files as $file) {
            $class = '\\XGallery\\Applications\\Cli\\Commands\\Flickr\\'.ucfirst(
                    basename($file->getFilename(), '.php')
                );
            $this->add((new $class));
        }
    }

    /**
     * @param $status
     * @return integer
     */
    protected function complete($status = 0)
    {
        return $status;
    }
}