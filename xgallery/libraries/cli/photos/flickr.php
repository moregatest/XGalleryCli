<?php

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCliPhotosFlickr extends JApplicationCli
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
		$db       = \Joomla\CMS\Factory::getDbo();

		// Transaction: Get a contact then fetch all photos of this
		try
		{
			$db->transactionStart();

			$query = ' SELECT ' . $db->quoteName('urls') . ',' . $db->quoteName('owner')
				. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
				. ' WHERE ' . $db->quoteName('state') . ' = 2'
				. ' AND ' . $db->quoteName('owner') . ' = ' . $db->quote('148429737@N03')
				. ' ORDER BY ' . $db->quoteName('id') . ' DESC'
				. ' LIMIT 500 OFFSET 0 FOR UPDATE ';
			$photos = $db->setQuery($query)->loadObjectList();

			foreach ($photos as $photo)
			{
				$urls  = json_decode($photo->urls);
				$url   = end($urls->sizes->size);

				$toDir = JPATH_ROOT . '/media/xgallery/' . $photo->owner;
				$fileName         = basename($url->source);
				$saveTo           = $toDir . '/' . $fileName;

				if (is_file($saveTo))
				{
					var_dump(getimagesize($saveTo));
					exit();
				}

				exit();
			}




			$db->transactionCommit();
		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query));
			$db->transactionRollback();
		}
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliPhotosFlickr')->execute();
