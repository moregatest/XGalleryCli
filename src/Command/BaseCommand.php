<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command;

use App\DefinesCore;
use App\Traits\HasEntityManager;
use App\Traits\HasLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

/**
 * Class BaseCommand
 * @package XGallery
 */
class BaseCommand extends Command
{
    use HasLogger;
    use HasEntityManager;

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

    const EXECUTE_SUCCEED = 0;

    /**
     * @var SymfonyStyle
     */
    protected $io;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ParameterBag
     */
    protected $params;
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var array
     */
    private $prepares = [];
    /**
     * @var array
     */
    private $processes = [];

    /**
     * BaseCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->params        = $parameterBag;

        parent::__construct();
    }

    /**
     * Clean up
     */
    public function __destruct()
    {
        $this->entityManager->close();
        $this->entityManager->getConnection()->close();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $className = get_called_class();
        $className = explode('\\', $className);
        $command   = strtolower($className[2] . ':' . str_replace($className[2], '', end($className)));

        $this->setName($command);
        $this->addOption(
            'task',
            null,
            InputOption::VALUE_OPTIONAL,
            'Execute specific task'
        );

        parent::configure();
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
        /**
         * @TODO Use https://symfony.com/doc/current/console/calling_commands.html
         */
        return new Process(
            array_merge(['php', $this->params->get('kernel.project_dir') . '/bin/console'], $cmd),
            null,
            null,
            null,
            (float)$timeout
        );
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
        /**
         * Custom console
         */
        $output->getFormatter()->setStyle(
            'head',
            new OutputFormatterStyle('yellow', 'black', ['bold'])
        );
        $output->getFormatter()->setStyle(
            'stage',
            new OutputFormatterStyle('green', 'black', ['bold'])
        );

        $output->getFormatter()->setStyle(
            'debug',
            new OutputFormatterStyle('magenta', 'black', ['bold', 'underscore'])
        );

        $output->getFormatter()->setStyle(
            'info',
            new OutputFormatterStyle('white', 'black', [])
        );

        $output->getFormatter()->setStyle(
            'notice',
            new OutputFormatterStyle('yellow', 'black', [])
        );

        $output->getFormatter()->setStyle(
            'succeed',
            new OutputFormatterStyle('blue', 'black', ['bold'])
        );

        $output->getFormatter()->setStyle(
            'warning',
            new OutputFormatterStyle('red', 'black', ['bold'])
        );

        $output->getFormatter()->setStyle(
            'error',
            new OutputFormatterStyle('white', 'red', [])
        );

        $output->getFormatter()->setStyle(
            'critical',
            new OutputFormatterStyle('white', 'red', ['bold'])
        );

        $output->getFormatter()->setStyle(
            'alert',
            new OutputFormatterStyle('white', 'red', ['bold', 'underscore'])
        );

        $output->getFormatter()->setStyle(
            'emergency',
            new OutputFormatterStyle('white', 'red', ['bold', 'underscore', 'blink'])
        );

        $this->input = $input;
        $this->io    = new SymfonyStyle($input, $output);
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
        if ($this->prepare() === self::PREPARE_FAILED) {
            $this->log('Prepare failed', 'warning', [], true);

            return 1;
        }

        $this->log('Prepare succeed', 'succeed', [], true);

        return $this->executeComplete($this->process());
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
            $this->io->success('Completed ' . $this->getName() . ': ' . (int)$status);

            return self::EXECUTE_SUCCEED;
        }

        $this->io->error('Completed ' . $this->getName() . ': ' . (int)$status);

        return $status;
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
            $this->io->writeln("\n");

            return true;
        }

        $this->log('Process: ' . implode(',', $this->processes));

        foreach ($this->processes as $process) {
            $this->log('<stage>' . $process . ' ...</stage>', 'info');
            $result = call_user_func([$this, $process]);

            if (!$result) {
                $this->log($process . ' failed', 'error');

                return false;
            }

            $this->log('Process succeed', 'succeed', [], true);
        }

        $this->io->newLine();

        return true;
    }

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
     * Wrapped method to display console output and log to file
     *
     * @param string $message
     * @param string $type
     * @param array $context
     * @param boolean $newLine
     */
    protected function log($message, $type = 'info', $context = [], $newLine = false)
    {
        $this->io->write("\n<$type>$message</$type>");

        if (!method_exists($this, 'log' . ucfirst($type))) {
            $type = 'info';
        }

        $this->{'log' . ucfirst($type)}(strip_tags($message), $context);

        if ($newLine === false) {
            return;
        }

        $this->io->newLine();
    }

    /**
     * Prepare data before execute command
     *
     * @return boolean
     */
    protected function prepare()
    {
        $this->io->write('Prepares: ' . implode(',', $this->prepares));

        foreach ($this->prepares as $prepare) {
            $this->log('<stage>' . $prepare . ' ...</stage>');

            $return = call_user_func([$this, $prepare]);

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
}
