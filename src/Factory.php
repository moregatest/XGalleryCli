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

class Factory
{
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

	public static function getDbo()
	{
		static $instance;

		if (isset($instance))
		{
			return $instance;
		}

		$factory  = new DatabaseFactory;
		$instance = $factory->getDriver('mysqli', array(
			'host'     => 'localhost',
			'user'     => 'root',
			'password' => 'root',
			'database' => 'soulevil_xgallery',
			'prefix'   => ''
		));


		return $instance;
	}

	/**
	 * @param string $level
	 *
	 * @return Logger
	 * @throws \Exception
	 */
	public static function getLogger($level = LogLevel::DEBUG)
	{
		static $logger;

		if (isset($logger))
		{
			return $logger;
		}

		$logger = new Logger('XGallery');
		$logger->pushHandler(new StreamHandler(XPATH_LOG . 'log_' . $level . '.log'), $level);

		return $logger;
	}

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