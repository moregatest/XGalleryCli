<?php

namespace XGallery\Applications;

use Symfony\Component\Console\Application;
use XGallery\Defines\DefinesCore;

/**
 * Class AbstractApplicationCli
 *
 * @package XGallery\Applications
 */
class AbstractApplicationCli extends Application
{

    /**
     * AbstractApplicationCli constructor.
     * @param string $name
     * @param string $version
     */
    public function __construct(
        $name = DefinesCore::APPLICATION,
        $version = DefinesCore::VERSION
    ) {
        parent::__construct($name, $version);
    }
}