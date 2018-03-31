<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment;

use XGallery\Factory;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Helper
 *
 * @since       2.0.0
 */
class Helper
{
	/**
	 * @param   string  $command Execute command
	 * @param   boolean $output  Show output
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public static function exec($command, $output = false)
	{
		$exec[] = 'php';
		$exec[] = $command;

		$result = false;

		if (!$output)
		{
			if (self::isWindows())
			{
				$exec   = "start /B " . implode(' ', $exec);
				$result = pclose(popen($exec, "r"));
			}
			else
			{
				$exec[] = '> /dev/null 2>/dev/null &';
				$exec   = implode(' ', $exec);
				$result = shell_exec($exec);
			}
		}

		Factory::getLogger()->info($exec, array($result));

		return $result;
	}

	/**
	 * @param   array $args Args
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
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
