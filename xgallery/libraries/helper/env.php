<?php

class XgalleryHelperEnv
{
	public static function exec($command)
	{
		$exec[] = $command;
		$exec[] = '> /dev/null 2>/dev/null &';

		XgalleryHelperLog::getLogger()->info('Execute ', $exec);

		return exec(implode(' ', $exec));
	}
}