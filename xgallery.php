<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Entrypoint
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/xgallery/bootstrap.php';

/**
 * @package     XGallery.Cli
 *
 * @since       2.0.0
 */
class XgalleryCli extends \Joomla\CMS\Application\CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function doExecute()
	{
		\Joomla\CMS\Factory::$application = $this;

		$input = \Joomla\CMS\Factory::getApplication()->input->cli;

		$class = '\\XGallery\\Application\\'
			. $input->getCmd('service', XGALLERY_DEFAULT_SERVICE)
			. '\\' . $input->getCmd('application', XGALLERY_DEFAULT_APPLICATION);
		$application = \XGallery\Application::getInstance($class);
		$application->execute();
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
\Joomla\CMS\Application\CliApplication::getInstance('XgalleryCli')->execute();
