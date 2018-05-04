<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Cli
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use Joomla\Registry\Registry;
use XGallery\AbstractApplication;
use XGallery\Environment\Helper;
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
class Cli extends AbstractApplication
{
	/**
	 * Application constructor.
	 *
	 * @param   Registry|null $config Configuration
	 *
	 * @throws  \Exception
	 */
	public function __construct(Registry $config = null)
	{
		parent::__construct($config);

		$this->input = $this->input->cli;
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

	/**
	 * @return  boolean
	 *
	 * @since   2.1.0
	 */
	protected function doExecute()
	{
		return true;
	}
}