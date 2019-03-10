<?php

namespace XGallery\Traits;

use XGallery\Factory;

/**
 * Trait HasLogger
 * @package XGallery\Traits
 */
trait HasLogger
{

    private function getLogger()
    {
        static $loggers;

        $id = md5(get_called_class());

        if (isset($loggers[$id])) {
            return $loggers[$id];
        }

        $loggers[$id] = Factory::getLogger(get_called_class());

        return $loggers[$id];
    }

    protected function logDebug($message, $context = [])
    {
        $this->getLogger()->debug($message, $context);
    }

    protected function logInfo($message, $context = [])
    {
        $this->getLogger()->info($message, $context);
    }

    protected function logNotice($message, $context = [])
    {
        $this->getLogger()->notice($message, $context);
    }

    protected function logWarning($message, $context = [])
    {
        $this->getLogger()->warning($message, $context);
    }

    protected function logError($message, $context = [])
    {
        $this->getLogger()->error($message, $context);
    }

    protected function logCritical($message, $context = [])
    {
        $this->getLogger()->critical($message, $context);
    }

    protected function logAlert($message, $context = [])
    {
        $this->getLogger()->alert($message, $context);
    }

    protected function logEmergency($message, $context = [])
    {
        $this->getLogger()->emergency($message, $context);
    }
}