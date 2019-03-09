<?php

namespace XGallery\Events;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CliEvent
 * @package XGallery\Events
 */
class CliEvent extends Event
{

    private $input;
    private $output;

    public function __construct(Input $input, Output $output)
    {
        $this->input = $input;
        $this->output = $output;
    }
}