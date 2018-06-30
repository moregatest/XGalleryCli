<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Filesystem
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment\Filesystem;

use XGallery\Environment;
use XGallery\Factory;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Filesystem.Helper
 *
 * @since       2.0.0
 */
class Helper
{
	/**
	 * @param   string $url    URL
	 * @param   string $saveTo Save to
	 *
	 * @return  boolean|integer
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public static function downloadFile($url, $saveTo)
	{
		Factory::getLogger()->info(__FUNCTION__, func_get_args());

		$ch = curl_init($url);

		curl_setopt_array($ch, array(
				CURLOPT_VERBOSE        => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_AUTOREFERER    => false,
				CURLOPT_ENCODING       => 'gzip,deflate',
				CURLOPT_HEADER         => 0,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
			)
		);

		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_REFERER, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, true);

		if (!is_resource($ch))
		{
			return false;
		}

		try
		{
			$result = curl_exec($ch);

			if ($result === false)
			{
				Factory::getLogger()->error('Download failed', array('url' => $url));
				curl_close($ch);

				return false;
			}

			$fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

			if ($fileSize <= 0)
			{
				Factory::getLogger()->error('Download failed', array('url' => $url));
				curl_close($ch);

				return false;
			}

			// The following lines write the contents to a file in the same directory (provided permissions etc)
			$fp = fopen($saveTo, 'w');

			if ($fp === false)
			{
				return false;
			}

			fwrite($fp, $result);
			fclose($fp);

			curl_close($ch);

			Factory::getLogger()->info('Download completed', array('url' => $url, 'to' => $saveTo));

			return $fileSize;
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage());
		}

		return false;
	}

	/**
	 * @param   string $from Download from
	 * @param   string $to   Save to
	 *
	 * @throws  \Exception
	 * @return  boolean
	 *
	 * @since  2.1.0
	 */
	public static function wget($from, $to)
	{
		$command = 'wget ' . $from . ' -O ' . $to;

		return Environment::exec($command, false);
	}
}
