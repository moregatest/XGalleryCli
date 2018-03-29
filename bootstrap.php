<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Bootstrap
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

define('_XEXEC', true);
define('XPATH_BASE', __DIR__);

$_SERVER['HTTP_HOST'] = null;

if (function_exists('xdebug_disable'))
{
	xdebug_disable();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/defines.php';

require_once __DIR__ . '/3rd/httpclient/http.php';
require_once __DIR__ . '/3rd/oauth-api/oauth_client.php';