<?php

// No direct access.
defined('_XEXEC') or die;

class XgalleryHelperEnv
{
	/**
	 * @param  string $command
	 *
	 * @return string
	 *
	 * @since  2.0.0
	 */
	public static function exec($command)
	{
		$exec[] = 'php';
		$exec[] = $command;
		$exec[] = '> /dev/null 2>/dev/null &';

		XgalleryHelperLog::getLogger()->info('Execute ', $exec);

		return exec(implode(' ', $exec));
	}

	public static function execService($service, $task)
	{
		return self::exec(XPATH_LIBRARIES . '/cli/' . $service . '/' . $task . '.php');
	}
}