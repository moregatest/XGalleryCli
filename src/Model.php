<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Base
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

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
	 * @param $name
	 *
	 * @return mixed
	 *
	 * @since  2.0.0
	 */
	public static function getInstance($name)
	{
		static $instances;

		if (!isset($instances[$name]))
		{
			$class            = '\\XGallery\Model\\' . ucfirst($name);
			$instances[$name] = new $class;
		}

		return $instances[$name];
	}

	/**
	 *
	 * @return null|object
	 *
	 * @since  2.0.0
	 */
	public function getMaxConnection()
	{
		return $this->getDbo()->setQuery('show variables like \'max_connections\'')->loadObject();
	}

	public function getDbo()
	{
		return \XGallery\Factory::getDbo();
	}
}
