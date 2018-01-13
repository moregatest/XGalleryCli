<?php

require_once __DIR__ . '/xgallery/bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		\Joomla\CMS\Factory::$application = $this;

		$input = \Joomla\CMS\Factory::getApplication()->input->cli;

		$service = $input->get('service', 'flickr');
		$task    = $input->get('task', 'contacts');

		XgalleryHelperEnv::execService($service, $task);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCli')->execute();
