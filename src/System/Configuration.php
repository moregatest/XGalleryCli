<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Configuration
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\System;

use Joomla\Filesystem\File;

/**
 * @package     XGallery.System
 * @subpackage  Configuration
 *
 * @since       2.0.0
 */
class Configuration
{
	/**
	 * @var    object|null
	 *
	 * @since  2.0.0
	 */
	protected $data = null;

	/**
	 * Configuration constructor.
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		$this->data = new \stdClass;

		if (is_file(XPATH_CONFIGURATION_FILE)
			&& file_exists(XPATH_CONFIGURATION_FILE)
		)
		{
			$buffer = file_get_contents(XPATH_CONFIGURATION_FILE);

			if ($buffer)
			{
				$this->data = json_decode($buffer);
			}
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
	public function getConfig($name, $default = null)
	{
		if (isset($this->data->{$name}))
		{
			$default = $this->data->{$name};
		}

		return $default;
	}

	/**
	 * @param   string $name  Name
	 * @param   mixed  $value Value
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function setConfig($name, $value)
	{
		$this->data->{$name} = $value;
	}

	/**
	 *
	 * @return boolean
	 *
	 * @since  2.0.0
	 */
	public function save()
	{
		$buffer = json_encode($this->data);

		return File::write(XPATH_CONFIGURATION_FILE, $buffer);
	}
}
