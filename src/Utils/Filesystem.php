<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Utils;

/**
 * Class Filesystem
 * @package App\Utils
 */
class Filesystem
{
    /**
     * @param $dir
     */
    public static function mkdir($dir)
    {
        self::getFilesystem()->mkdir($dir);
    }

    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    protected static function getFilesystem()
    {
        static $instance;

        if ($instance) {
            return $instance;
        }

        $instance = new \Symfony\Component\Filesystem\Filesystem;

        return $instance;
    }

    /**
     * @param $dir
     * @return bool
     */
    public static function exists($dir)
    {
        return self::getFilesystem()->exists($dir);
    }

    /**
     * @param $origin
     * @param $target
     * @param bool $overwrite
     */
    public static function rename($origin, $target, $overwrite = false)
    {
        self::getFilesystem()->rename($origin, $target, $overwrite);
    }
}
