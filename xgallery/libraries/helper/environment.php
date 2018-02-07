<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Libraries.Helper
 *
 * @since       2.0.0
 */
class XgalleryHelperEnvironment
{
	/**
	 * @param   string $command Execute command
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function exec($command)
	{
		$exec[] = 'php';
		$exec[] = $command;
		$exec[] = '> /dev/null 2>/dev/null &';

		XgalleryHelperLog::getLogger()->info('Execute ', $exec);

		return exec(implode(' ', $exec));
	}

	/**
	 * @param   string $service  Service
	 * @param   string $task     Task
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function execService($service, $task)
	{
		return self::exec(XPATH_LIBRARIES . '/cli/' . $service . '/' . $task . '.php');
	}
}
