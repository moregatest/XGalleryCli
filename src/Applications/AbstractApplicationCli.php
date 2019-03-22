<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Defines\DefinesCore;
use XGallery\Traits\HasLogger;

/**
 * Class AbstractApplicationCli
 *
 * @package XGallery\Applications
 */
abstract class AbstractApplicationCli extends Application
{
    use HasLogger;

    /**
     * ApplicationCli constructor
     *
     * @param string $name
     * @param string $version
     * @throws Exception
     */
    public function __construct($name = DefinesCore::APPLICATION, $version = DefinesCore::VERSION)
    {
        parent::__construct($name, $version);

        $this->registerCommands();
    }

    /**
     * Register all required commands
     *
     * @return mixed
     */
    abstract protected function registerCommands();

    /**
     * Wrapped run with complete method
     *
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     * @return integer
     * @throws Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->complete(parent::run($input, $output));
    }

    /**
     * Trigger after application run completed
     *
     * @param $status
     * @return integer
     */
    abstract protected function complete($status = 0);
}
