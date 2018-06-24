<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service\Flickr;

use XGallery\Oauth\Service\Flickr;

defined('_XEXEC') or die;

/**
 * Class People
 * @package   XGallery\Service\Flickr
 *
 * @since     2.1.0
 */
class People extends Flickr
{
	/**
	 * @param   string $nsid   Nsid
	 * @param   array  $photos Photo
	 * @param   array  $params Parameters
	 *
	 * @return  array
	 *
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	public function getPhotosList($nsid, &$photos = array(), $params = array())
	{
		$return = $this->getPhotos(
			array_merge(
				array(
					'safe_search' => XGALLERY_FLICKR_SAFE_SEARCH,
					'user_id'     => $nsid
				),
				$params
			)
		);

		if ($return && $return->stat == 'ok')
		{
			$photos = array_merge($photos, $return->photos->photo);

			if ($return->photos->pages > $return->photos->page)
			{
				$this->getPhotosList($nsid, $photos, array('page' => (int) $return->photos->page + 1));
			}
		}

		return $photos;
	}

	/**
	 * @param   array $params Parameters
	 *
	 * @return  boolean|object
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	protected function getPhotos($params)
	{
		return $this->execute(
			array_merge(
				array(
					'method'   => 'flickr.people.getPhotos',
					'per_page' => XGALLERY_FLICKR_PEOPLE_GETPHOTOS_PERPAGE
				), $params
			)
		);
	}
}
