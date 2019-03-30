<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Traits;

use Exception;
use Monolog\Logger;
use XGallery\Factory;

/**
 * Trait HasLogger
 * @package XGallery\Traits
 */
trait HasLogger
{

    /**
     * Get instance of logger
     *
     * @return mixed|Logger
     * @throws Exception
     */
    private function getLogger()
    {
        static $loggers;

        $id = md5(static::class);

        if (isset($loggers[$id])) {
            return $loggers[$id];
        }

        $loggers[$id] = Factory::getLogger(static::class);

        return $loggers[$id];
    }

    /**
     * Log with debug level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logDebug($message, $context = [])
    {
        $this->getLogger()->debug($message, $context);
    }

    /**
     * Log with info level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logInfo($message, $context = [])
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * Log with notice level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logNotice($message, $context = [])
    {
        $this->getLogger()->notice($message, $context);
    }

    /**
     * Log with warning level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logWarning($message, $context = [])
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * Log with error level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logError($message, $context = [])
    {
        $this->getLogger()->error($message, $context);
    }

    /**
     * Log with critical level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logCritical($message, $context = [])
    {
        $this->getLogger()->critical($message, $context);
    }

    /**
     * Log with alert level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logAlert($message, $context = [])
    {
        $this->getLogger()->alert($message, $context);
    }

    /**
     * Log with emergency level
     *
     * @param string $message
     * @param array  $context
     * @throws Exception
     */
    protected function logEmergency($message, $context = [])
    {
        $this->getLogger()->emergency($message, $context);
    }
}
