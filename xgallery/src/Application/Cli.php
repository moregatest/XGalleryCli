<?php
/**
 * @package     XGallery\Application
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace XGallery\Application;

use Joomla\CMS\Factory;
use XGallery\Application;
use XGallery\Environment\Helper;

/**
 * @package     XGallery\Application
 *
 * @since       2.0.0
 */
class Cli extends Application
{
	/**
	 * @var    \JInput
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
		$this->input = Factory::getApplication()->input->cli;
	}

	/**
	 * @param   string $service     Service
	 * @param   string $application Appication
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	protected function subTask($service, $application)
	{
		$args                = $this->input->getArray();
		$args['service']     = $service;
		$args['application'] = $application;

		return Helper::execService($args);
	}
}
