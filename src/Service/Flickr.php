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
use XGallery\Service\Flickr\Contacts;
use XGallery\Service\Flickr\People;
use XGallery\Service\Flickr\Photos;
use XGallery\Service\Flickr\Urls;


defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @property     Photos   $photos
 * @property     Contacts $contacts
 * @property     People   $people
 * @property     Urls     $url
 *
 * @since       2.0.0
 */
class Flickr extends \XGallery\Oauth\Service\Flickr
{
	/**
	 * @param   string $name Variable name
	 *
	 * @return  boolean|mixed
	 * @since   2.1.0
	 */
	public function __get($name)
	{
		$name = ucfirst($name);

		if (File::exists(__DIR__ . '/Flickr/' . $name . '.php'))
		{
			return call_user_func(XGALLERY_NAMESPACE . '\\Service\\Flickr\\' . $name . '::getInstance');
		}

		return false;
	}
}
