<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Webservices\Services;

use XGallery\Webservices\Services\Flickr\Traits\Contacts;
use XGallery\Webservices\Services\Flickr\Traits\Favorites;
use XGallery\Webservices\Services\Flickr\Traits\People;
use XGallery\Webservices\Services\Flickr\Traits\Photos;
use XGallery\Webservices\Services\Flickr\Traits\Urls;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @since       2.0.0
 */
class Flickr extends \XGallery\Webservices\Oauth\Flickr
{
	use Contacts;
	use Favorites;
	use People;
	use Photos;
	use Urls;

	/**
	 * @return Flickr
	 * @throws \Exception
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		static $instance;

		if ($instance)
		{
			return $instance;
		}

		$instance = new static;

		return $instance;
	}
}
