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

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use XGallery\Defines\DefinesCore;

/**
 * Trait HasLogger
 * @package App\Traits
 */
trait HasLogger
{

    /**
     * getLogger
     *
     * @param string $name
     * @return boolean|Logger
     */
    private function getLogger($name = null)
    {
        static $loggers;

        if ($name === null) {
            $name = static::class;
        }

        if (isset($loggers[$name])) {
            return $loggers[$name];
        }

        $loggers[$name] = new Logger(DefinesCore::APPLICATION);
        $logFile        = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($name));

        try {
            $loggers[$name]->pushHandler(
                new StreamHandler(
                    getenv('log_path') . '/' . uniqid($logFile . '_' . date('Y-m-d') . '_' . time(), true) . '.log'
                )
            );

            return $loggers[$name];
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Log with debug level
     *
     * @param string $message
     * @param array $context
     */
    protected function logDebug($message, $context = [])
    {
        $this->getLogger()->debug($message, $context);
    }

    /**
     * Log with info level
     *
     * @param string $message
     * @param array $context
     */
    protected function logInfo($message, $context = [])
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * Log with notice level
     *
     * @param string $message
     * @param array $context
     */
    protected function logNotice($message, $context = [])
    {
        $this->getLogger()->notice($message, $context);
    }

    /**
     * Log with warning level
     *
     * @param string $message
     * @param array $context
     */
    protected function logWarning($message, $context = [])
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * Log with error level
     *
     * @param string $message
     * @param array $context
     */
    protected function logError($message, $context = [])
    {
        $this->getLogger()->error($message, $context);
    }

    /**
     * Log with critical level
     *
     * @param string $message
     * @param array $context
     */
    protected function logCritical($message, $context = [])
    {
        $this->getLogger()->critical($message, $context);
    }

    /**
     * Log with alert level
     *
     * @param string $message
     * @param array $context
     */
    protected function logAlert($message, $context = [])
    {
        $this->getLogger()->alert($message, $context);
    }

    /**
     * Log with emergency level
     *
     * @param string $message
     * @param array $context
     */
    protected function logEmergency($message, $context = [])
    {
        $this->getLogger()->emergency($message, $context);
    }
}
