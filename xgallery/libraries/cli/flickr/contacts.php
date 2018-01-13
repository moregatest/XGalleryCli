<?php

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCliFlickrContacts extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		\Joomla\CMS\Factory::$application = $this;

		XgalleryModelFlickr::getInstance()->insertContactsFromFlickr();

		// Fetch photos
		XgalleryHelperEnv::execService('flickr', 'photos');
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrContacts')->execute();
