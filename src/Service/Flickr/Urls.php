<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service\Flickr;

defined('_XEXEC') or die;

/**
 * Class Urls
 * @package   XGallery\Service\Flickr
 *
 * @since     2.1.0
 */
class Urls extends \XGallery\Oauth\Service\Flickr
{
	/**
	 * @param   string $nsid Url
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.1.0
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
	 * @since   2.1.0
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
