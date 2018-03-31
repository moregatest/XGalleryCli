<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Filesystem
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment\Filesystem;

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
	 * @throws \Exception
	 */
	public static function downloadFile($url, $saveTo)
	{
		Factory::getLogger()->info(__FUNCTION__, func_get_args());

		$ch = curl_init();

		curl_setopt_array($ch, array(
				CURLOPT_URL            => $url,
				CURLOPT_VERBOSE        => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_AUTOREFERER    => false,
				CURLOPT_ENCODING       => 'gzip,deflate',
				CURLOPT_HEADER         => 0,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
			)
		);

		try
		{
			if (!is_resource($ch))
			{
				return false;
			}

			$result   = curl_exec($ch);
			$fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

			if ($result === false)
			{
				Factory::getLogger()->error('Download failed', array('url' => $url));
				curl_close($ch);

				return false;
			}

			// The following lines write the contents to a file in the same directory (provided permissions etc)
			$fp = fopen($saveTo, 'w');
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
}
