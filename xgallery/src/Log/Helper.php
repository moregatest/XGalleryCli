<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Log.Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Log;

// No direct access.
use Katzgrau\KLogger\Logger;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Log.Helper
 *
 * @since       2.0.0
 */
class Helper
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
			$logger = New Logger(
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
