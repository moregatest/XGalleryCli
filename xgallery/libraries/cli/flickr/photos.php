<?php

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCliFlickrPhotos extends JApplicationCli
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

		$input = \Joomla\CMS\Factory::getApplication()->input->cli;
		$db    = \Joomla\CMS\Factory::getDbo();

		// Custom args
		$url  = $input->get('url', null, 'RAW');
		$nsid = $input->get('nsid', null);

		$xgallery = XgalleryFlickr::getInstance();
		$model    = XgalleryModelFlickr::getInstance();

		// Get nsid from URL
		if ($url)
		{
			$nsid = $xgallery->lookupUser($url);

			if ($nsid && $nsid->stat == "ok")
			{
				$nsid = $nsid->user->id;
			}
		}

		// Transaction: Get a contact then fetch all photos of this contact
		try
		{
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

		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			XgalleryHelperLog::getLogger()->warning('No nsid provided');

			return;
		}

		// Fetch photos
		$photos = $xgallery->getPhotosList($nsid);

		// Insert photos list
		if (!empty($photos))
		{
			$query = ' INSERT IGNORE INTO ' . $db->quoteName('#__xgallery_flickr_contact_photos')
				. '( ' . implode(',', $db->quoteName(array(
					'id',
					'owner',
					'secret',
					'server',
					'farm',
					'title',
					'ispublic',
					'isfriend',
					'isfamily',
					'urls',
					'state'
				))) . ' )'
				. ' VALUES ';

			foreach ($photos as $photo)
			{
				$values   = array();
				$values[] = isset($photo->id) ? $db->quote($photo->id) : $db->quote('');
				$values[] = isset($photo->owner) ? $db->quote($photo->owner) : $db->quote('');
				$values[] = isset($photo->secret) ? $db->quote($photo->secret) : $db->quote('');
				$values[] = isset($photo->server) ? $db->quote($photo->server) : $db->quote('');
				$values[] = isset($photo->farm) ? $db->quote($photo->farm) : $db->quote('');
				$values[] = isset($photo->title) ? $db->quote($photo->title) : $db->quote('');
				$values[] = isset($photo->ispublic) ? $db->quote($photo->ispublic) : $db->quote('');
				$values[] = isset($photo->isfriend) ? $db->quote($photo->isfriend) : $db->quote('');
				$values[] = isset($photo->isfamily) ? $db->quote($photo->isfamily) : $db->quote('');
				$values[] = isset($photo->urls) ? $db->quote($photo->urls) : $db->quote('');
				$values[] = isset($photo->state) ? $db->quote($photo->state) : 0;

				$query .= ' ( ' . implode(',', $values) . '),';
			}

			$query = trim($query, ',');

			// Try to execute INSERT IGNORE
			try
			{
				// Insert photos list. Ignore duplicate
				$db->setQuery($query)->execute();
			}
			catch (Exception $exception)
			{
				XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query));
			}

			// Only process if this user have any photos
			try
			{
				$model->downloadPhotos($nsid, 150, 0);
			}
			catch (Exception $exception)
			{
				XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query));
			}

			$db->disconnect();
		}
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrPhotos')->execute();
