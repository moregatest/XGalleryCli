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

defined('_XEXEC') or die;

/**
 * @package     XGallery\Application
 *
 * @since       2.0.0
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
	 * @since  2.0.0
	 */
	public function __construct()
	{
		$this->input = \XGallery\Factory::getInput()->cli;

		// $this->install();
	}

	/**
	 * @param   string $application Appication
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	protected function subTask($application)
	{
		$args                = $this->input->getArray();
		$args['application'] = $application;

		return Helper::execService($args);
	}
}
