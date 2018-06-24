<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Filesystem
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment\Filesystem;

use Joomla\Filesystem\Folder;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Filesystem.Directory
 *
 * @since       2.0.0
 */
class Directory extends Folder
{
	/**
	 * @param   string $directory Directory path
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public static function getDirectories($directory)
	{
		$list = array();
		$dir  = new \DirectoryIterator($directory);

		foreach ($dir as $fileInfo)
		{
			if ($fileInfo->isDir() && !$fileInfo->isDot())
			{
				$list [] = $fileInfo->getFilename();
			}
		}

		return $list;
	}

	/**
	 * @param   string $directory Directory path
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public static function getFiles($directory)
	{
		$list = array();
		$dir  = new \DirectoryIterator($directory);

		foreach ($dir as $fileInfo)
		{
			if ($fileInfo->isFile() && !$fileInfo->isDot())
			{
				$list[] = $fileInfo->getFilename();
			}
		}

		return $list;
	}

	/**
	 * @param   string $dir Directory path
	 *
	 * @return  boolean
	 */
	public static function exists($dir)
	{
		return is_dir($dir) && file_exists($dir);
	}
}
