<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Utils;


class Filesystem
{
    public static function mkdir($dir)
    {
        self::getFilesystem()->mkdir($dir);
    }

    protected static function getFilesystem()
    {
        static $instance;

        if ($instance) {
            return $instance;
        }

        $instance = new \Symfony\Component\Filesystem\Filesystem;

        return $instance;
    }

    public static function exists($dir)
    {
        self::getFilesystem()->exists($dir);
    }
}
