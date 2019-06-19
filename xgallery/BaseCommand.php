<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

declare(strict_types=1);
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery;

use App\DefinesCore;
use App\Traits\HasConsole;
use App\Traits\HasEntityManager;
use App\Traits\HasLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Process\Process;

/**
 * Class BaseCommand
 * @package XGallery\Command
 */
class BaseCommand extends Command
{
    use LockableTrait;
    use HasLogger;
    use HasConsole;
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
     * Clean up
     */
    public function __destruct()
    {
        $this->entityManager->getConnection()->close();
        //$this->release();
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
     * @return boolean|string
     */
    protected function getBinDir()
    {
        return realpath(__DIR__ . '/../bin');
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
        return new Process(array_merge(['php', $this->getBinDir() . '/console'], $cmd), null, null, null, $timeout);
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
        $store      = new FlockStore;
        $factory    = new Factory($store);
        $this->lock = $factory->createLock($this->getName());

        if (!$this->lock->acquire()) {
            return 0;
        }

        $this->input  = $input;
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
        if ($this->prepare() === self::PREPARE_FAILED) {
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
        $this->output->write('Prepares: ' . implode(',', $this->prepares));

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

        if (!method_exists($this, 'log' . ucfirst($type))) {
            $type = 'info';
        }

        $this->{'log' . ucfirst($type)}(strip_tags($message), $context);

        if ($newLine === false) {
            return;
        }

        $this->output->writeln('');
    }
}
