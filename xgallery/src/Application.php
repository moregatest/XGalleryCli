<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Base
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Application
 * @subpackage  Base
 *
 * @since       2.0.0
 */
class Application
{
	/**
	 * @param   string $name Classname
	 *
	 * @return  object
	 *
	 * @since   2.0.0
	 */
	public static function getInstance($name)
	{
		static $instances;

		if (!isset($instances[$name]))
		{
			if (class_exists($name) && is_subclass_of($name, '\\XGallery\\Application'))
			{
				$instances[$name] = new $name;
			}
			else
			{
				$instances[$name] = new static;
			}
		}

		return $instances[$name];
	}

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function execute()
	{
		return true;
	}
}
