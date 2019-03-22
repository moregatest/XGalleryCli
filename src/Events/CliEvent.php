<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

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
        $this->input  = $input;
        $this->output = $output;
    }
}
