<?php

namespace XGallery\Applications;

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
     * ApplicationCli constructor.
     * @param string $name
     * @param string $version
     * @throws \Exception
     */
    public function __construct($name = DefinesCore::APPLICATION, $version = DefinesCore::VERSION)
    {
        parent::__construct($name, $version);

        $this->registerCommands();
    }

    abstract protected function registerCommands();

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return integer
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->complete(parent::run($input, $output));
    }

    /**
     * @param $status
     * @return integer
     */
    abstract protected function complete($status = 0);
}