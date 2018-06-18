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
 * Class Photos
 * @package   XGallery\Service\Flickr
 *
 * @since     2.1.0
 */
class Photos extends Flickr
{
	/**
	 * @param   string $pid Pid
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	public function getPhotoSizes($pid)
	{
		if (empty($pid))
		{
			return false;
		}

		return $this->execute(array(
				'method'   => 'flickr.photos.getSizes',
				'photo_id' => $pid
			)
		);
	}
}
