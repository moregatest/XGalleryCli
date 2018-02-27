<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

defined('_XEXEC') or die;

use Joomla\CMS\Factory;
use XGallery\Application\Base;
use XGallery\Environment\Helper;
use XGallery\Model\Flickr;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @since       2.0.0
 */
class Contacts extends Base
{
	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 * @throws \Exception
	 */
	public function execute()
	{
		parent::execute();

		$input = Factory::getApplication()->input->cli;

		$result = Flickr::getInstance()->insertContactsFromFlickr();

		$args                = $input->getArray();
		$args['service']     = 'Flickr';
		$args['application'] = 'Photos';

		Helper::execService($args);

		return $result;
	}
}
