<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Traits\HasLogger;
use XGallery\Traits\HasObject;

/**
 * Class AbstractCommand
 * @package XGallery\Applications\Cli
 */
abstract class AbstractCommand extends Command
{
    use HasObject;
    use HasLogger;

    /**
     * Ignore this prepare. Move to next
     */
    const NEXT_PREPARE = 1;

    /**
     * Prepare failed. Escape prepare with failed
     */
    const PREPARE_FAILED = false;

    const PREPARE_SUCCEED = true;

    /**
     * Complete prepare. Move to process directly
     */
    const SKIP_PREPARE = 2;

    /**
     * Array of options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Array of args
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Input
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Output
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Main progressBar
     *
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * Wrapped method to get input option
     *
     * @param      $name
     * @param null $default
     * @return boolean|string|string[]|null
     */
    protected function getOption($name, $default = null)
    {
        $value = $this->input->getOption($name);

        if (!$value) {
            return $default;
        }

        return $value;
    }

    /**
     * Configures the current command.
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        if (!empty($this->options)) {
            foreach ($this->options as $key => $options) {
                $this->addOption(
                    $key,
                    isset($options['shortcut']) ? $options['shortcut'] : null,
                    isset($options['mode']) ? $options['mode'] : InputOption::VALUE_OPTIONAL,
                    isset($options['description']) ? $options['description'] : '',
                    isset($options['default']) ? $options['default'] : null
                );
            }
        }

        $this->addOption('task', null, InputOption::VALUE_OPTIONAL, 'Execute specific task');

        if (!empty($this->arguments)) {
            foreach ($this->arguments as $key => $arguments) {
                $this->addArgument(
                    $key,
                    isset($arguments['mode']) ? $arguments['mode'] : InputArgument::OPTIONAL,
                    isset($arguments['description']) ? $arguments['description'] : '',
                    isset($arguments['default']) ? $arguments['default'] : null
                );
            }
        }
    }

    /**
     * Wrapped execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer|null null or 0 if everything went fine, or an error code. 1 for normal escape
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        // Can not prepare then exit execute
        if ($this->prepare() === self::PREPARE_FAILED) {
            $this->log('Prepare failed', 'notice', [], true);

            return 1;
        }

        $this->log('Prepare succeed', 'success', [], true);

        return $this->executeComplete($this->process());
    }

    /**
     * Prepare data before execute command
     *
     * @return boolean
     */
    protected function prepare()
    {
        $this->log(__FUNCTION__, 'head');
        $this->progressBar = $this->getProgressBar();

        $classes = get_class_methods($this);

        foreach ($classes as $class) {
            if ($class === 'prepare' || strpos($class, 'prepare') === false) {
                continue;
            }

            $this->log($class.' ...', 'stage');

            $return = call_user_func([$this, $class]);

            if ($return === self::PREPARE_FAILED) {
                return false;
            } elseif ($return === self::NEXT_PREPARE) {
                $this->log('Skip this prepare. Move to next');
                continue;
            } elseif ($return === self::SKIP_PREPARE) {
                $this->log('Skip prepare. Move to process');

                return true;
            }
        }

        return true;
    }

    /**
     * Process endpoint
     *
     * @return boolean
     */
    protected function process()
    {
        $this->log(__FUNCTION__, 'head');

        $task = $this->getOption('task');

        if ($task && method_exists($this, $task)) {
            return $this->{$task}();
        }

        $classes = get_class_methods($this);
        $steps   = [];

        foreach ($classes as $class) {
            if ($class === 'process' || strpos($class, 'process') === false) {
                continue;
            }

            $steps[] = $class;
        }

        if (empty($steps)) {
            $this->output->writeln("\n");

            return true;
        }

        $this->log('Steps: '.implode(',', $steps), 'steps', [], true);
        $this->progressBar->setMaxSteps(count($steps));

        foreach ($steps as $step) {
            $this->log($step.' ...', 'stage');
            $result = call_user_func([$this, $step]);

            if (!$result) {
                $this->log($step.' failed', 'error');

                return false;
            }

            $this->output->write("\n");
            $this->progressBar->advance();

            $this->log('Process succeed', 'success');
        }

        $this->output->write("\n");

        return true;
    }

    /**
     * Execute completed
     *
     * @param boolean $status
     * @return mixed|integer
     */
    protected function executeComplete($status)
    {
        $this->log('Completed '.$this->getName().': '.(int)$status, 'info', [], true);

        if ($status === true) {
            return 0;
        }

        return $status;
    }

    /**
     * Wrapped method to display console output and log to file
     *
     * @param string  $message
     * @param string  $type
     * @param array   $context
     * @param boolean $newLine
     */
    protected function log($message, $type = 'info', $context = [], $newLine = false)
    {
        $this->output->getFormatter()->setStyle('head', new OutputFormatterStyle('white', 'green', ['bold']));
        $this->output->getFormatter()->setStyle('stage', new OutputFormatterStyle('green', 'black', ['bold']));
        $this->output->getFormatter()->setStyle('steps', new OutputFormatterStyle('yellow', 'black', ['bold']));
        $this->output->getFormatter()->setStyle('success', new OutputFormatterStyle('blue', 'black', ['bold']));

        $this->output->getFormatter()->setStyle('notice', new OutputFormatterStyle('red', 'black'));
        $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'black', ['bold']));
        $this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('white', 'red', ['bold']));

        $mapType = [
            'head' => 'info',
            'stage' => 'info',
            'steps' => 'info',
            'success' => 'info',
            'comment' => 'info',
            'question' => 'info',
        ];

        if ($type !== 'info') {
            $this->output->write("\n<$type>$message</$type>");
        } else {
            $this->output->write("\n".$message);
        }

        $this->{'log'.ucfirst(isset($mapType[$type]) ? $mapType[$type] : $type)}($message, $context);

        if ($newLine === false) {
            return;
        }

        if ($newLine === true) {
            $this->output->writeln('');

            return;
        }

        $this->output->writeln($newLine);
    }

    protected function getModel($name)
    {
    }

    protected function getProgressBar($max = 0)
    {
        $progressBar = new ProgressBar($this->output, $max);
        $progressBar->setFormat('debug');

        return $progressBar;
    }
}
