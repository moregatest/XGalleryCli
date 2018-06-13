<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

use XGallery\Application\Flickr;

defined('_XEXEC') or die;

/**
 * Class Cli
 * @package      XGallery\Application
 * @subpackage   Flickr\Cli
 *
 * @since        2.1.0
 */
class Cli extends Flickr
{
	protected function doExecute()
	{
		var_dump($this->service->getGroup());
	}
}