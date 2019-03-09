<?php

namespace XGallery\Traits;

use XGallery\Factory;

trait HasLogger
{

    protected function logDebug($message, $context = [])
    {
        Factory::getLogger(get_class($this))->debug($message, $context);
    }

    protected function logInfo($message, $context = [])
    {
        Factory::getLogger(get_class($this))->info($message, $context);
    }


    protected function logNotice($message, $context = [])
    {
        Factory::getLogger(get_class($this))->notice($message, $context);
    }

    protected function logWarning($message, $context = [])
    {
        Factory::getLogger(get_class($this))->warning($message, $context);
    }

    protected function logError($message, $context = [])
    {
        Factory::getLogger(get_class($this))->error($message, $context);
    }

    protected function logCritical($message, $context = [])
    {
        Factory::getLogger(get_class($this))->critical($message, $context);
    }

    protected function logAlert($message, $context = [])
    {
        Factory::getLogger(get_class($this))->alert($message, $context);
    }

    protected function logEmergency($message, $context = [])
    {
        Factory::getLogger(get_class($this))->emergency($message, $context);
    }
}