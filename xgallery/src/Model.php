<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Base
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Joomla\CMS\Factory;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Base
 *
 * @since       2.0.0
 */
class Model
{
	/**
	 *
	 * @return static
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{
			$instance = new static;
		}

		return $instance;
	}

	/**
	 *
	 * @return null|object
	 *
	 * @since  2.0.0
	 */
	public function getMaxConnection()
	{
		return Factory::getDbo()->setQuery('show variables like \'max_connections\'')->loadObject();
	}
}
