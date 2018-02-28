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
require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/defines.php';

/**
 * @param   string  $class  Classname
 *
 * @return  boolean
 *
 * @since   2.0.0
 */
function autoloadPsr4($class)
{
	$file = XPATH_SRC . str_replace('\\', '/', $class) . '.php';

	if (is_file($file) && file_exists($file))
	{
		return require $file;
	}

	return false;
}

spl_autoload_register('autoloadPsr4');
