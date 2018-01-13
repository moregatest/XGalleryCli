<?php

// No direct access.
defined('_XEXEC') or die;

class XgalleryHelperLog
{
	/**
	 * @param string $level
	 *
	 * @return \Katzgrau\KLogger\Logger
	 */
	public static function getLogger($level = \Psr\Log\LogLevel::DEBUG)
	{
		static $logger;

		if (!isset($logger))
		{
			$logger = New \Katzgrau\KLogger\Logger(
				JPATH_ROOT . '/media/xgallery/', $level,
				array
				(
					'prefix' => 'log_' . $level . '_'
				)
			);
		}

		return $logger;
	}
}