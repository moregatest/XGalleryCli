<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment;

// No direct access.
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
	 */
	public static function exec($command, $output = false)
	{
		$exec[] = 'php';
		$exec[] = $command;

		if (!$output)
		{
			$exec[] = '> /dev/null 2>/dev/null &';
		}

		\XGallery\Log\Helper::getLogger()->info(__FUNCTION__, $exec);

		return exec(implode(' ', $exec));
	}

	/**
	 * @param   string $service Service
	 * @param   string $task    Task
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function execService($service, $task)
	{
		return self::exec(XPATH_CLI . '/' . $service . '/' . $task . '.php');
	}
}
