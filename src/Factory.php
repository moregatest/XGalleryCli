<?php
/**
 * Created by PhpStorm.
 * User: soulevilx
 * Date: 3/29/18
 * Time: 9:25 AM
 */

namespace XGallery;


use Joomla\Database\DatabaseFactory;
use Joomla\Input\Input;

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

	public static function getLogger()
	{

	}
}