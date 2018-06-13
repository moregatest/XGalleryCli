<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service\Flickr\Traits;

defined('_XEXEC') or die;

/**
 * Trait Urls
 * @package    XGallery\Service\Flickr\Traits
 * @subpackage Urls
 */
Trait Urls
{
	/**
	 * @param   string $nsid Url
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public function getGroup($nsid)
	{
		if (empty($nsid))
		{
			return false;
		}

		return $this->execute(array(
				'method'   => 'flickr.urls.getGroup',
				'group_id' => $nsid
			)
		);
	}

	/**
	 * @param   string $url Url
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	public function lookupUser($url)
	{
		if (empty($url))
		{
			return false;
		}

		return $this->execute(array(
				'method' => 'flickr.urls.lookupUser',
				'url'    => $url
			)
		);
	}
}
