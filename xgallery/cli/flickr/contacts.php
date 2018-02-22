<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Entrypoint
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @package     XGallery.Cli
 * @subpackage  Flickr.Contacts
 *
 * @since       2.0.0
 */
class XgalleryCliFlickrContacts extends \Joomla\CMS\Application\CliApplication
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

		\XGallery\Model\Flickr::getInstance()->insertContactsFromFlickr();

		// Fetch photos
		XGallery\Environment\Helper::execService('flickr', 'photos');
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrContacts')->execute();
