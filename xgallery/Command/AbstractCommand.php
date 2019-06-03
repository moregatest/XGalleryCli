<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Command;

use App\Traits\HasConsole;
use App\Traits\HasLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use XGallery\Defines\DefinesCommand;
use XGallery\Defines\DefinesCore;

/**
 * Class AbstractCommand
 * @package XGallery\Command
 */
abstract class AbstractCommand extends Command
{
    use HasLogger;
    use HasConsole;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var array
     */
    private $prepares = [];

    /**
     * @var array
     */
    private $processes = [];

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
     */
    protected function configure()
    {
        $this->addOption('task', null, InputOption::VALUE_OPTIONAL, 'Execute specific task');
    }

    /**
     * Wrapped method to get Process
     *
     * @param array $cmd
     * @param integer $timeout
     * @return Process
     */
    protected function getProcess($cmd, $timeout = DefinesCore::MAX_EXECUTE_TIME)
    {
        /*        $cmd = array_merge(
                    [
                        'php',
                        XGALLERY_PATH.'/bin/application',
                    ],
                    $cmd
                );*/

        /**
         * @TODO Use https://symfony.com/doc/current/console/calling_commands.html
         */
        return new Process($cmd, null, null, null, $timeout);
    }

    /**
     * Wrapped execute method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer|mixed|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->io = new SymfonyStyle($input, $output);

        $this->initConsoleStyle();

        $this->io->title(get_called_class());

        $classes = get_class_methods($this);

        foreach ($classes as $class) {

            if ($class !== 'prepare' && strpos($class, 'prepare') !== false) {
                $this->prepares[] = $class;
            }

            if ($class !== 'process' && strpos($class, 'process') !== false) {
                $this->processes[] = $class;
            }
        }

        // No prepare required
        if (empty($this->prepares)) {
            return $this->executeComplete($this->process());
        }

        // Can not prepare then exit execute
        if ($this->prepare() === DefinesCommand::PREPARE_FAILED) {
            $this->log('Prepare failed', 'warning', [], true);

            return 1;
        }

        $this->log('Prepare succeed', 'succeed', [], true);

        return $this->executeComplete($this->process());
    }

    /**
     * Prepare data before execute command
     *
     * @return boolean
     */
    protected function prepare()
    {
        $this->output->write('Prepares: '.implode(',', $this->prepares));

        foreach ($this->prepares as $prepare) {
            $this->log('<stage>'.$prepare.' ...</stage>');

            $return = call_user_func([$this, $prepare]);

            if ($return === DefinesCommand::PREPARE_FAILED) {
                return false;
            } elseif ($return === DefinesCommand::NEXT_PREPARE) {
                $this->log('Skip this prepare. Move to next');
                continue;
            } elseif ($return === DefinesCommand::SKIP_PREPARE) {
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
        $task = $this->getOption('task');

        if ($task && method_exists($this, $task)) {
            return $this->{$task}();
        }

        if (empty($this->processes)) {
            $this->output->writeln("\n");

            return true;
        }

        $this->log('Process: '.implode(',', $this->processes));

        foreach ($this->processes as $process) {
            $this->log('<stage>'.$process.' ...</stage>', 'info');
            $result = call_user_func([$this, $process]);

            if (!$result) {
                $this->log($process.' failed', 'error');

                return false;
            }

            $this->log('Process succeed', 'succeed', [], true);
        }

        $this->io->newLine();

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
        if ($status === true) {
            $this->io->success('Completed '.$this->getName().': '.(int)$status);

            return DefinesCommand::EXECUTE_SUCCEED;
        }

        $this->io->error('Completed '.$this->getName().': '.(int)$status);

        return $status;
    }

    /**
     * Wrapped method to display console output and log to file
     *
     * @param string $message
     * @param string $type
     * @param array $context
     * @param boolean $newLine
     */
    protected function log($message, $type = 'info', $context = [], $newLine = false)
    {
        $this->output->write("\n<$type>$message</$type>");

        if (!method_exists($this, 'log'.ucfirst($type))) {
            $type = 'info';
        }

        $this->{'log'.ucfirst($type)}(strip_tags($message), $context);

        if ($newLine === false) {
            return;
        }

        $this->output->writeln('');
    }

    protected function getTemplate()
    {
        $filesystemLoader = new FilesystemLoader(XGALLERY_PATH.'/templates/command/%name%');

        $templating = new PhpEngine(new TemplateNameParser, $filesystemLoader);
        echo $templating->render('hello.php', ['firstname' => 'Fabien']);
    }

    /**
     * @param $entity
     * @param $index
     */
    protected function batchInsert($entity, $index)
    {
        $this->entityManager->persist($entity);

        // flush everything to the database every bulk inserts
        if (($index % DefinesCore::BATCH_SIZE) == 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
