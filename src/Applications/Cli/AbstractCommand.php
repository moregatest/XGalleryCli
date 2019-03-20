<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Factory;
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
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
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
        if ($this->prepare() === false) {
            $this->log('Prepare failed', 'notice', [], true);

            return 1;
        }

        $this->log('Prepare succeed. Starting ...');

        return $this->executeComplete($this->process());
    }

    /**
     * Prepare data before execute command
     *
     * @throws DBALException
     */
    protected function prepare()
    {
        $this->log(__FUNCTION__);
        $this->connection  = Factory::getConnection();
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('debug');

        $classes = get_class_methods($this);

        foreach ($classes as $class) {
            if (strpos($class, 'prepare', 0) === false || $class === 'prepare') {
                continue;
            }

            $this->log($class.' ...');

            $return = call_user_func([$this, $class]);

            if ($return === false) {
                return false;
            } elseif ($return === 1) { // Force return prepare succeed
                return true;
            } elseif ($return === -1) { // Process next prepare
                continue;
            }
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function process()
    {
        $classes = get_class_methods($this);
        $steps   = [];

        foreach ($classes as $class) {
            if (strpos($class, 'process', 0) === false || $class === 'process') {
                continue;
            }

            $steps[] = $class;
        }

        if (!empty($steps)) {
            $this->log('Steps: '.implode(',', $steps), 'info', [], true);
            $this->progressBar->start(count($steps));

            foreach ($steps as $step) {
                $this->log($step.' ...');
                $result = call_user_func([$this, $step]);
                if (!$result) {
                    $this->log($step.' failed', 'notice');

                    return false;
                }
                $this->output->write("\n");
                $this->progressBar->advance();
                $this->log('Succeed', 'info');
            }
        }

        return true;
    }

    /**
     * @param $status
     * @return mixed
     */
    protected function executeComplete($status)
    {
        $this->log('Completed '.$this->getName().': '.(int)$status, 'info', [], true);

        $this->connection->close();

        if ($status === true) {
            return 0;
        }

        return $status;
    }

    /**
     * @param        $message
     * @param string $type
     * @param        $context
     * @param bool   $newLine
     */
    protected function log($message, $type = 'info', $context = [], $newLine = false)
    {
        $this->output->write("\n".$message);

        call_user_func([$this, 'log'.ucfirst($type)], $message, $context);

        if ($newLine === false) {
            return;
        }

        if ($newLine === true) {
            $this->output->writeln('');

            return;
        }

        $this->output->writeln($newLine);
    }
}