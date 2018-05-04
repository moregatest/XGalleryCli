<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Filesystem
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment\Filesystem;

/**
 * Class File
 * @package     XGallery\Environment\Filesystem
 * @subpackage  Filesystem.File
 *
 * @since       2.0.2
 */
class File extends \Joomla\Filesystem\File
{
	/**
	 * @param   string $file File path
	 *
	 * @return boolean
	 *
	 * @since       2.0.2
	 */
	public static function exists($file)
	{
		return is_file($file) && file_exists($file);
	}

	/**
	 * @param   string $file File path
	 *
	 * @return boolean|string
	 *
	 * @since       2.0.2
	 */
	public static function read($file)
	{
		if (!self::exists($file))
		{
			return false;
		}

		return file_get_contents($file);
	}
}
