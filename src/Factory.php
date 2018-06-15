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
use Stash\Driver\Apc;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;
use XGallery\Service\Flickr;
use XGallery\System\Configuration;

defined('_XEXEC') or die;

/**
 * Class Factory
 * @package XGallery
 *
 * @since   2.0.2
 */
class Factory
{
	/**
	 * @param   string $name Application name
	 *
	 * @return  boolean
	 *
	 * @since   2.0.2
	 */
	public static function getApplication($name)
	{
		static $instances;

		$name      = str_replace('.', '\\', $name);
		$className = '\\' . XGALLERY_NAMESPACE . '\\Application\\' . $name;

		if (isset($instances[$className]))
		{
			return $instances[$name];
		}

		if (!class_exists($className) && !is_subclass_of($name, '\\' . XGALLERY_NAMESPACE . '\\Application'))
		{
			return false;
		}

		$instances[$name] = new $className;

		return $instances[$name];
	}

	/**
	 * @return  Input
	 *
	 * @since   2.0.2
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
	 * @since   2.0.2
	 */
	public static function getDbo()
	{
		static $instance;

		if (isset($instance))
		{
			return $instance;
		}

		$config   = Configuration::getInstance();
		$factory  = new DatabaseFactory;
		$instance = $factory->getDriver('mysqli',
			array(
				'host'     => $config->get('host'),
				'user'     => $config->get('user'),
				'password' => $config->get('password'),
				'database' => $config->get('database'),
				'prefix'   => $config->get('prefix')
			)
		);

		return $instance;
	}

	/**
	 * @param   string $name  Name
	 * @param   string $level Log level
	 *
	 * @return  Logger
	 *
	 * @since   2.0.2
	 *
	 * @throws \Exception
	 */
	public static function getLogger($name = 'core', $level = LogLevel::DEBUG)
	{
		static $instances;

		$name = str_replace('\\', '_', strtolower($name));

		if (isset($instances[$name]))
		{
			return $instances[$name];
		}

		$instances[$name] = new Logger(XGALLERY_NAMESPACE);
		$instances[$name]->pushHandler(new StreamHandler(XPATH_LOG . date("Y-m-d", time()) . '/' . $name . '_' . $level . '.log'));

		return $instances[$name];
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
		$className = '\\' . XGALLERY_NAMESPACE . '\\Service\\' . $name;

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

	/**
	 * @return Configuration
	 */
	public static function getConfiguration()
	{
		return Configuration::getInstance();
	}

	/**
	 * @param   string $driver Driver
	 *
	 * @return  Cache
	 */
	public static function getCache($driver = null)
	{
		static $caches;

		if (isset($caches[$driver]))
		{
			return $caches[$driver];
		}

		if ($driver === null)
		{
			$driver = self::getConfiguration()->get('cache_driver', 'FileSystem');
		}

		switch ($driver)
		{
			default:
			case 'FileSystem':
				$cacheDriver = new FileSystem(array('path' => XPATH_CACHE));
				break;
			case 'APC':
				$cacheDriver = new Apc(array('ttl' => 3600));
				break;
			case 'Memcache':
				$cacheDriver = new Memcache(array('servers' => array('127.0.0.1', '11211')));
				break;
		}

		$caches[$driver] = new Cache($cacheDriver);

		return $caches[$driver];
	}
}
