<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Cli
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use XGallery\Application;
use XGallery\Environment\Helper;
use XGallery\Factory;
use XGallery\System\Configuration;

defined('_XEXEC') or die;

/**
 * Class Cli
 * @package      XGallery\Application
 * @subpackage   Cli
 *
 * @since        2.0.0
 *
 */
class Cli extends Application
{
	/**
	 * @var    \Joomla\Input\Cli
	 * @since  2.0.0
	 */
	protected $input;

	/**
	 * Cli constructor.
	 *
	 * @throws \Exception
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		$this->input = Factory::getInput()->cli;
	}

	/**
	 * @return string
	 *
	 * @throws \Exception
	 *
	 * @since  2.0.0
	 */
	public function install()
	{
		$config  = Configuration::getInstance();
		$command = 'mysql --user=' . $config->get('user') . ' --password=' . $config->get('password') . ' ' . $config->get('database') . ' < ' . XPATH_ROOT . '/install.sql';

		return Helper::exec($command, false);
	}
}
