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
class XgalleryHelperLog
{
	/**
	 * @param   string $level Level
	 *
	 * @return \Katzgrau\KLogger\Logger
	 *
	 * @since       2.0.0
	 */
	public static function getLogger($level = \Psr\Log\LogLevel::DEBUG)
	{
		static $logger;

		if (!isset($logger))
		{
			$logger = New \Katzgrau\KLogger\Logger(
				XPATH_LOG, $level,
				array
				(
					'prefix' => 'log_' . $level . '_'
				)
			);
		}

		return $logger;
	}
}
