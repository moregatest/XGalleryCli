<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use XGallery\Model;

/**
 * Class Flickr
 * @package      XGallery\Application
 * @subpackage   Cli.Flickr
 *
 * @since        2.0.0
 *
 */
class Flickr extends Cli
{
	/**
	 * @param   string $name Model name
	 *
	 * @return Model\Flickr|mixed
	 */
	protected function getModel($name = 'Flickr')
	{
		return Model::getInstance($name);
	}
}
