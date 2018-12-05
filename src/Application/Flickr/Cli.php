<?php
/**
 * @package     XGalleryCli.Application
 * @subpackage  Flickr.Cli
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

use XGallery\Application\Flickr;
use XGallery\Entities\Flickr\EntityContact;

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
		$data = $this->input->getArray();

		unset($data['application']);
		unset($data['method']);

		$result = call_user_func_array(
			[
				$this->service, $this->input->getCmd('method')
			], $data
		);

		switch ($this->input->getCmd('method'))
		{
			case 'lookupUser':
				$entityContact = EntityContact::find($result->user->id);
				print_r($entityContact);
				break;
		}

		return parent::doExecute();
	}
}
