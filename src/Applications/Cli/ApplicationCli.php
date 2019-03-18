<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli;

use Symfony\Component\Finder\Finder;
use XGallery\Applications\AbstractApplicationCli;

/**
 * Class ApplicationCli
 * @package XGallery\Applications\Cli
 */
class ApplicationCli extends AbstractApplicationCli
{
    /**
     *
     */
    protected function registerCommands()
    {
        $folders = (new Finder())->directories()->in(__DIR__.'/Commands')->depth(0);

        foreach ($folders as $folder) {
            $dirName = $folder->getFilename();
            $files   = (new Finder())->files()->in(__DIR__.'/Commands/'.$dirName)->depth(0)->name('*.php');
            foreach ($files as $file) {
                $class = '\\XGallery\\Applications\\Cli\\Commands\\'.$dirName.'\\'.ucfirst(
                        basename($file->getFilename(), '.php')
                    );
                $this->add((new $class));
            }
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