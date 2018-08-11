<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Helper
 *
 * @since       2.0.0
 */
class Environment
{
	/**
	 * @param   string  $command Execute command
	 * @param   boolean $isPhp   PHP execute
	 * @param   boolean $output  Show output
	 *
	 * @return  string|boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public static function exec($command, $isPhp = true, $output = false)
	{
		$execute = array();

		if ($isPhp)
		{
			$execute[] = 'php';
		}

		$execute[] = $command;

		$result = false;

		if (self::isWindows())
		{
			$execute  = "start /B " . implode(' ', $execute);
			$resource = popen($execute, "r");

			if ($resource === false)
			{
				return false;
			}

			$result = pclose($resource);

			Factory::getLogger('Exec')->info('Exec', array($execute, $result));

			return $result;
		}

		if (!$output)
		{
			$execute[] = '> /dev/null 2>/dev/null &';
			$execute   = implode(' ', $execute);
			$result    = shell_exec($execute);
		}

		Factory::getLogger('Exec')->info('Exec', array($execute, $result));

		return $result;
	}

	/**
	 * @param   array $args Args
	 *
	 * @return  string|boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public static function execService($args = array())
	{
		$command = XPATH_ROOT . '/xgallery.php';

		if (!empty($args))
		{
			foreach ($args as $name => $value)
			{
				$command .= ' --' . $name . '=' . $value;
			}
		}

		return self::exec(trim($command));
	}

	/**
	 *
	 * @return boolean
	 *
	 * @since  2.0.0
	 */
	public static function isWindows()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			return false;
		}

		return true;
	}
}
