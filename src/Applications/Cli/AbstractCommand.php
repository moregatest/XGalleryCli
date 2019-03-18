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
     * @throws Exception
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
     * @param InputInterface $input
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
            $this->output->writeln('Prepare failed');
            $this->progressBar->finish();
            $this->output->writeln('');

            return 1;
        }

        // Finish prepare step
        $this->progressBar->advance();
        $this->info('Prepare successed');

        return $this->executeComplete($this->process());
    }

    /**
     * Prepare data before execute command
     *
     * @throws DBALException
     */
    protected function prepare()
    {
        $this->info(__FUNCTION__, [], true);

        $this->connection  = Factory::getConnection();
        $this->progressBar = new ProgressBar($this->output, 0);
        $this->progressBar->setFormat('debug');
        $this->progressBar->start(1);
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        if (!empty($steps)) {
            $this->progressBar->setMaxSteps(count($steps) + 1);
            $this->info('Process steps: '.implode(',', $steps));
            foreach ($steps as $step) {
                $this->info($step.' ...');
                $result = call_user_func([$this, $step]);
                $this->output->writeln('');
                $this->progressBar->advance();

                if (!$result) {
                    return false;
                }
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
        $this->info('Completed '.$this->getName().': '.(int)$status."\n");

        $this->connection->close();

        if ($status === true) {
            return 0;
        }

        return $status;
    }

    /**
     * @param $message
     * @param array $context
     * @param boolean $newLine
     */
    protected function info($message, $context = [], $newLine = false)
    {
        $this->output->write("\n".$message);
        $this->logInfo($message, $context);

        if ($newLine) {
            $this->output->writeln('');
        }
    }
}