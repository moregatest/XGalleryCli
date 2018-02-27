<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment;

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

		return shell_exec(implode(' ', $exec));
	}

	/**
	 * @param   array $args Args
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function execService($args = array())
	{
		$command = JPATH_ROOT . '/cli/xgallery.php';

		if (!empty($args))
		{
			foreach ($args as $name => $value)
			{
				$command .= ' --' . $name . '=' . $value;
			}
		}

		return self::exec(trim($command));
	}
}
