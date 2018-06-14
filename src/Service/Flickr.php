<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service;

use XGallery\Environment\Filesystem\File;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @since       2.0.0
 */
class Flickr extends \XGallery\Oauth\Service\Flickr
{
	/**
	 * @param   string $name  Variable name
	 *
	 * @return  boolean|mixed
	 * @since   2.1.0
	 */
	public function __get($name)
	{
		$name = ucfirst($name);

		if (File::exists(__DIR__ . '/Flickr/' . $name . '.php'))
		{
			return call_user_func('XGallery\\Service\\Flickr\\' . $name . '::getInstance');
		}

		return false;
	}
}
