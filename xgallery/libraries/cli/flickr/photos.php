<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Entrypoint
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/../../../bootstrap.php';


/**
 * @package     XGallery.Cli
 * @subpackage  Flickr.Photos
 *
 * @since       2.0.0
 */
class XgalleryCliFlickrPhotos extends \Joomla\CMS\Application\CliApplication
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
		$model = XgalleryModelFlickr::getInstance();

		// Custom args
		$url  = $input->get('url', null, 'RAW');
		$nsid = $input->get('nsid', null);

		// Get nsid from URL
		if ($url)
		{
			$nsid = \XGallery\Flickr\Flickr::getInstance()->lookupUser($url);

			if ($nsid && $nsid->stat == "ok")
			{
				$nsid = $nsid->user->id;
			}
		}

		// Transaction: Get a contact then fetch all photos of this contact
		try
		{
			$db = \Joomla\CMS\Factory::getDbo();

			$db->transactionStart();

			if ($nsid === null)
			{
				$nsid = $model->getContact();
			}

			$model->updateContact($nsid);

			$db->transactionCommit();
		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $db->getQuery()));
			$db->transactionRollback();
		}

		$model->insertPhotosFromFlickr($nsid);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrPhotos')->execute();
