<?php
/**
 * @package     XGallery.Cli
 * @subpackage  OAuth
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
	protected $data = null;

	public function __construct()
	{
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

	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{
			$instance = new static();
		}

		return $instance;
	}

	public function getConfig($name, $default = null)
	{
		if (isset($this->data->{$name}))
		{
			$default = $this->data->{$name};
		}

		return $default;
	}

	public function setConfig($name, $value)
	{
		$this->data->{$name} = $value;
	}

	public function save()
	{
		$buffer = json_encode($this->data);
		File::write(XPATH_CONFIGURATION_FILE, $buffer);
	}
}
