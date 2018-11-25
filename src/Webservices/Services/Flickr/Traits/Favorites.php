<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Webservices\Services\Flickr\Traits;

use XGallery\Webservices\Oauth\Flickr;

defined('_XEXEC') or die;

/**
 * Class Favorites
 * @package   XGallery\Service\Flickr
 *
 * @since     2.1.0
 */
trait Favorites
{
	abstract public function execute($parameters, $url = Flickr::API_ENDPOINT, $method = 'GET', $options = []);

	/**
	 * @param   string $nsid User id
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	public function getFavortiesList($nsid = null)
	{
		return $this->execute(array(
				'method'   => 'flickr.favorites.getList',
				'user_id'  => $nsid,
				'per_page' => XGALLERY_FLICKR_FAVORITES_GETLIST_PERPAGE
			)
		);
	}
}
