<?php

define('_XEXEC', true);
define('XPATH_BASE', __DIR__);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/libraries/vendor/httpclient/http.php';
require_once __DIR__ . '/libraries/vendor/oauth-api/oauth_client.php';
require_once __DIR__ . '/defines.php';
require_once __DIR__ . '/cli.php';

spl_autoload_register(function ($className) {
	$prefix      = 'Xgallery';
	$parts       = preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);
	$classPrefix = array_shift($parts);

	if ($classPrefix == $prefix)
	{
		$filePath = strtolower(XPATH_BASE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php');

		if (is_file($filePath) && file_exists($filePath))
		{
			require_once $filePath;
		}
	}
});