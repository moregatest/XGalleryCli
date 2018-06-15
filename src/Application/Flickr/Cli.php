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
	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 */
	protected function doExecute()
	{
		$data   = $this->input->getArray();
		$method = $data['method'];
		$method = explode('.', $method);

		unset($data['application']);
		unset($data['method']);

		print_r(call_user_func_array(array($this->service->{$method[0]}, $method[1]), $data));

		return parent::doExecute();
	}
}
