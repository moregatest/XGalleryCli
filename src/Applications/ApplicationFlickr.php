<?php

namespace XGallery\Applications;


use XGallery\Defines\DefinesCore;

/**
 * Class ApplicationFlickr
 *
 * @package XGallery\Applications\Commands
 */
class ApplicationFlickr extends AbstractApplicationCli
{

    protected $commands = [
        'Contacts',
        'Photos'
    ];

    /**
     * ApplicationFlickr constructor.
     * @param string $name
     * @param string $version
     */
    public function __construct(
        string $name = DefinesCore::APPLICATION,
        string $version = DefinesCore::VERSION
    )
    {
        parent::__construct($name, $version);

        foreach ($this->commands as $command) {
            $commandClass = '\\XGallery\\Applications\\Commands\\Flickr\\' . ucfirst($command);
            $this->add(new $commandClass);
        }
    }
}