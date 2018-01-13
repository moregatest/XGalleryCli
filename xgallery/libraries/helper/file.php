<?php

// No direct access.
defined('_XEXEC') or die;
class XgalleryHelperFile
{
	public static function downloadFile($url, $saveTo)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__, func_get_args());

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result   = curl_exec($ch);
		$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		if ($result)
		{
			// the following lines write the contents to a file in the same directory (provided permissions etc)
			$fp = fopen($saveTo, 'w');
			fwrite($fp, $result);
			fclose($fp);

			return $filesize;
		}

		curl_close($ch);

		XgalleryHelperLog::getLogger()->error('Download failed', array('url' => $url));

		return false;
	}
}
