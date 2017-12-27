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
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		\Joomla\CMS\Factory::$application = $this;

		$input = \Joomla\CMS\Factory::getApplication()->input->cli;

		$xgallery = XgalleryFlickr::getInstance();
		$db       = \Joomla\CMS\Factory::getDbo();
		$url      = $input->get('url');
		$nsid     = $input->get('nsid', null);

		if ($url)
		{
			$nsid = $xgallery->lookupUser($url);

			if ($nsid && $nsid->stat == "ok")
			{
				$nsid = $nsid->user->id;
			}
		}

		$photos = array();

		// Transaction: Get a contact then fetch all photos of this
		try
		{
			$db->transactionStart();

			if ($nsid === null)
			{
				// Fetch photos of a contact
				$rawQuery = ' SELECT ' . $db->quoteName('nsid')
					. ' FROM ' . $db->quoteName('#__xgallery_flickr_contacts')
					. ' ORDER BY ' . $db->quoteName('updated') . ' ASC'
					. ' LIMIT 1 FOR UPDATE;';
				$nsid     = $db->setQuery($rawQuery)->loadResult();
			}

			XgalleryHelperLog::getLogger()->info('Work on nsid: ' . $nsid);

			// Update to make sure another process won't step over
			$rawQuery = ' UPDATE ' . $db->quoteName('#__xgallery_flickr_contacts')
				. ' SET ' . $db->quoteName('updated') . ' = ' . $db->quote(\Joomla\CMS\Date\Date::getInstance()->toSql())
				. ' WHERE ' . $db->quoteName('nsid') . ' = ' . $db->quote($nsid);
			$db->setQuery($rawQuery)->execute();
			$db->transactionCommit();
		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $rawQuery));
			$db->transactionRollback();
		}

		if ($nsid)
		{
			$photos = $xgallery->getPhotosList($nsid);
		}

		// Insert photos list
		if (!empty($photos))
		{
			//$query = $db->getQuery(true);
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
				// Get photo sizes of current contact
				$query = 'SELECT ' . $db->quoteName('id')
					. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
					. ' WHERE ' . $db->quoteName('state') . ' = 0 '
					. ' AND ' . $db->quoteName('owner') . ' = ' . $db->quote($nsid)
					. ' LIMIT 300 OFFSET 0 ';
				$pids  = $db->setQuery($query)->loadColumn();

				foreach ($pids as $pid)
				{
					$sized = $xgallery->getPhotoSizes($pid);

					if (!$sized)
					{
						continue;
					}

					if ($sized->stat != "ok")
					{
						continue;
					}

					// Update sized
					$query = ' UPDATE ' . $db->quoteName('#__xgallery_flickr_contact_photos')
						. ' SET '
						. $db->quoteName('urls') . ' = ' . $db->quote(json_encode($sized))
						. ',' . $db->quoteName('state') . ' = 1'
						. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid);
					$db->setQuery($query)->execute();

					XgalleryHelperEnv::exec(__DIR__ . '/download.php --pid=' . $pid);
				}
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
