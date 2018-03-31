<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Factory
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Joomla\Database\DatabaseFactory;
use Joomla\Input\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use XGallery\Service\Flickr;

defined('_XEXEC') or die;

/**
 * Class Factory
 * @package XGallery
 *
 * @since   2.0.02
 */
class Factory
{
	/**
	 * @param   string $name Application name
	 *
	 * @return  boolean
	 *
	 * @since   2.0.02
	 */
	public static function getApplication($name)
	{
		static $instances;

		$name      = str_replace('.', '\\', $name);
		$className = '\\XGallery\\Application\\' . $name;

		if (isset($instances[$className]))
		{
			return $instances[$name];
		}

		if (!class_exists($className) && !is_subclass_of($name, '\\XGallery\\Application'))
		{
			return false;
		}

		$instances[$name] = new $className;

		return $instances[$name];
	}

	/**
	 * @return  Input
	 *
	 * @since   2.0.02
	 */
	public static function getInput()
	{
		static $instance;

		if (isset($instance))
		{
			return $instance;
		}

		$instance = new Input;

		return $instance;
	}

	/**
	 * @return  \Joomla\Database\DatabaseDriver
	 *
	 * @since   2.0.02
	 */
	public static function getDbo()
	{
		static $instance;

		if (isset($instance))
		{
			return $instance;
		}

		$factory  = new DatabaseFactory;
		$instance = $factory->getDriver('mysqli',
			array(
				'host'     => 'localhost',
				'user'     => 'root',
				'password' => 'root',
				'database' => 'soulevil_xgallery',
				'prefix'   => ''
			)
		);

		return $instance;
	}

	/**
	 * @param   string $level Log level
	 *
	 * @return  Logger
	 *
	 * @since   2.0.02
	 *
	 * @throws \Exception
	 */
	public static function getLogger($level = LogLevel::DEBUG)
	{
		static $instance;

		if (isset($instance))
		{
			return $instance;
		}

		$instance = new Logger('XGallery');
		$instance->pushHandler(new StreamHandler(XPATH_LOG . 'log_' . $level . '.log'));

		return $instance;
	}

	/**
	 * @param   string $name Service name
	 *
	 * @return  boolean|Flickr
	 *
	 * @since   2.0.0
	 */
	public static function getService($name)
	{
		static $instances;

		$name      = str_replace('.', '\\', $name);
		$className = '\\XGallery\\Service\\' . $name;

		if (isset($instances[$className]))
		{
			return $instances[$name];
		}

		if (!class_exists($className))
		{
			return false;
		}

		$instances[$name] = new $className;

		return $instances[$name];
	}
}
