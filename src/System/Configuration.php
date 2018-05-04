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
	 * @since  2.0.0
	 */
	public function __construct()
	{
		$this->config = new Registry;

		if ($buffer = File::read(XPATH_CONFIGURATION_FILE))
		{
			$this->config->loadString($buffer);
		}
	}

	/**
	 *
	 * @return  static
	 *
	 * @since   2.0.0
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
		return File::write(XPATH_CONFIGURATION_FILE, $this->config->toString());
	}
}
