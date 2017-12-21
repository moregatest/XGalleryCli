<?php

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCliFlickrDownload extends JApplicationCli
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

		$db  = \Joomla\CMS\Factory::getDbo();
		$pid = $_SERVER['argv'][1];

		XgalleryHelperLog::getLogger()->info('Download photo : ' . $pid);

		try
		{
			$db->transactionStart();
			$query = ' SELECT ' . $db->quoteName('urls') . ',' . $db->quoteName('owner')
				. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
				. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid)
				. ' LIMIT 1 FOR UPDATE ';
			$photo = $db->setQuery($query)->loadObject();
			$urls  = json_decode($photo->urls);
			$url   = end($urls->sizes->size);

			$toDir = JPATH_ROOT . '/media/xgallery/' . $photo->owner;
			\Joomla\Filesystem\Folder::create($toDir);
			$fileName         = basename($url->source);
			$saveTo           = $toDir . '/' . $fileName;
			$originalFileSize = XgalleryHelperFile::downloadFile($url->source, $saveTo);

			if ($originalFileSize === false || $originalFileSize != filesize($saveTo))
			{
				\Joomla\Filesystem\File::delete($saveTo);

				throw new Exception('Download failed: ' . filesize($saveTo) . '/' . $originalFileSize);
			}
			else
			{
				$query = $db->getQuery(true);
				// Update this photo status
				$query->clear()
					->update($db->quoteName('#__xgallery_flickr_contact_photos'))
					->set(array(
						$db->quoteName('state') . ' = 2'
					))
					->where($db->quoteName('id') . ' = ' . $db->quote($pid));
				$db->setQuery($query)->execute();

				$db->transactionCommit();

				XgalleryHelperLog::getLogger()->info('---- Download completed ' . $pid . ' ----', array('saveTo' => $saveTo));
			}

		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query, 'url' => get_object_vars($urls)));
			$db->transactionRollback();
		}

		$db->disconnect();
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrDownload')->execute();
