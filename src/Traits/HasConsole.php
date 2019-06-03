<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Traits;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait HasConsole
 * @package App\Traits
 */
trait HasConsole
{
    /**
     * Output
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     *
     */
    protected function initConsoleStyle()
    {
        /**
         * Custom console
         */
        $this->output->getFormatter()->setStyle(
            'head',
            new OutputFormatterStyle('yellow', 'black', ['bold'])
        );
        $this->output->getFormatter()->setStyle(
            'stage',
            new OutputFormatterStyle('green', 'black', ['bold'])
        );

        $this->output->getFormatter()->setStyle(
            'debug',
            new OutputFormatterStyle('magenta', 'black', ['bold', 'underscore'])
        );

        $this->output->getFormatter()->setStyle(
            'info',
            new OutputFormatterStyle('white', 'black', [])
        );

        $this->output->getFormatter()->setStyle(
            'notice',
            new OutputFormatterStyle('yellow', 'black', [])
        );

        $this->output->getFormatter()->setStyle(
            'succeed',
            new OutputFormatterStyle('blue', 'black', ['bold'])
        );

        $this->output->getFormatter()->setStyle(
            'warning',
            new OutputFormatterStyle('red', 'black', ['bold'])
        );

        $this->output->getFormatter()->setStyle(
            'error',
            new OutputFormatterStyle('white', 'red', [])
        );

        $this->output->getFormatter()->setStyle(
            'critical',
            new OutputFormatterStyle('white', 'red', ['bold'])
        );

        $this->output->getFormatter()->setStyle(
            'alert',
            new OutputFormatterStyle('white', 'red', ['bold', 'underscore'])
        );

        $this->output->getFormatter()->setStyle(
            'emergency',
            new OutputFormatterStyle('white', 'red', ['bold', 'underscore', 'blink'])
        );
    }
}
