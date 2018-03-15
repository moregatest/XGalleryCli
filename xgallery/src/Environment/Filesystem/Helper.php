<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Environment.Filesystem
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Environment\Filesystem;

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
	 */
	public static function downloadFile($url, $saveTo)
	{
		\XGallery\Log\Helper::getLogger()->info(__FUNCTION__, func_get_args());

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
			if (is_resource($ch))
			{
				$result   = curl_exec($ch);
				$fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

				if ($result === false)
				{
					\XGallery\Log\Helper::getLogger()->error('Download failed', array('url' => $url));

					return false;
				}

				// The following lines write the contents to a file in the same directory (provided permissions etc)
				$fp = fopen($saveTo, 'w');
				fwrite($fp, $result);
				fclose($fp);


				curl_close($ch);


				\XGallery\Log\Helper::getLogger()->info('Download completed', array('url' => $url));

				return $fileSize;
			}

			return false;
		}
		catch (\Exception $exception)
		{
			\XGallery\Log\Helper::getLogger()->error($exception->getMessage());
		}

		return false;
	}
}
