<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Configuration
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\System;

use Joomla\Registry\Registry;
use XGallery\Environment\Filesystem\File;

/**
 * @package     XGallery.System
 * @subpackage  Configuration
 *
 * @since       2.0.0
 */
class Configuration
{
	/**
	 * @var    Registry
	 *
	 * @since  2.0.0
	 */
	protected $config = null;

	/**
	 * Configuration constructor.
	 *
	 * @param   string $name Config file
	 *
	 * @since   2.0.0
	 */
	public function __construct($name)
	{
		$this->config = new Registry;
		$buffer       = File::read(XPATH_ROOT . '/' . $name);

		if ($buffer)
		{
			$this->config->loadString($buffer);
		}
	}

	/**
	 * @param   string $name Config file
	 *
	 * @return  static
	 */
	public static function getInstance($name = 'config.json')
	{
		static $instances;

		if (isset($instances[$name]))
		{
			return $instances[$name];
		}

		$instances[$name] = new static($name);

		return $instances[$name];
	}

	/**
	 * @param   string $name    Name
	 * @param   mixed  $default Default value
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0.
	 */
	public function get($name, $default = null)
	{
		return $this->config->get($name, $default);
	}

	/**
	 * @param   string $name  Name
	 * @param   mixed  $value Value
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function set($name, $value)
	{
		$this->config->set($name, $value);
	}

	/**
	 *
	 * @return boolean
	 *
	 * @since  2.0.0
	 */
	public function save()
	{
		$buffer = $this->config->toString();

		return File::write(XPATH_CONFIGURATION_FILE, $buffer);
	}
}
