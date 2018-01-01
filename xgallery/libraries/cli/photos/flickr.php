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

		$db = \Joomla\CMS\Factory::getDbo();

		// Transaction: Get a contact then fetch all photos of this
		try
		{
			$db->transactionStart();

			$query  = ' SELECT ' . $db->quoteName('pid')
				. ',' . $db->quoteName('urls')
				. ',' . $db->quoteName('owner')
				. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
				. ' WHERE ' . $db->quoteName('state') . ' = 2'
				. ' ORDER BY ' . $db->quoteName('id') . ' ASC'
				. ' LIMIT 100 OFFSET 0 FOR UPDATE ';
			$photos = $db->setQuery($query)->loadObjectList();

			if (!empty($photos))
			{
				$query = $db->getQuery(true);
				$query->insert($db->quoteName('#__xgallery_photos'));
				$query->columns($db->quoteName(array(
					'type',
					'dir',
					'filename',
					'width',
					'height',
					'ratio',
					'isWallpaper',
					'created',
					'params'
				)));

				$ids = array();

				foreach ($photos as $photo)
				{
					$ids[] = $photo->pid;
					$urls  = json_decode($photo->urls);
					$url   = end($urls->sizes->size);

					$photoDir      = 'media/xgallery/' . $photo->owner;
					$fileName      = basename($url->source);
					$photoFilepath = JPATH_ROOT . '/' . $photoDir . '/' . $fileName;
					$isWallpaper   = true;

					// File exists
					if (is_file($photoFilepath))
					{
						$imageSize = getimagesize($photoFilepath);
						$width     = $imageSize[0];
						$height    = $imageSize[1];

						// Verify
						$fileSize = filesize($photoFilepath);

						// File size condition: 500KB
						if ($fileSize < 512000)
						{
							$isWallpaper = false;
						}

						//
						if ($width < 1024 || $height < 768)
						{
							$isWallpaper = false;
						}

						$this->resize(1280, 1024, $photoDir, $fileName);
						$this->resize(1366, 768, $photoDir, $fileName);
						$this->resize(1920, 1080, $photoDir, $fileName);
						$this->resize(1920, 1200, $photoDir, $fileName);
						$this->resize(2560, 1080, $photoDir, $fileName);
						$this->resize(2560, 1440, $photoDir, $fileName);
						$this->resize(3440, 1440, $photoDir, $fileName);
						$this->resize(3840, 2160, $photoDir, $fileName);

						$values   = array();
						$values[] = $db->quote('flickr');
						$values[] = $db->quote($photoDir);
						$values[] = $db->quote($fileName);
						$values[] = (float) $width;
						$values[] = (float) $height;
						$values[] = (float) $width / (float) $height;
						$values[] = (int) $isWallpaper;
						$values[] = $db->quote(\Joomla\CMS\Date\Date::getInstance()->toSql());
						$values[] = $db->quote('');

						$query->values(implode(',', $values));
					}
				}

				$query = str_replace('INSERT', 'INSERT IGNORE', (string) $query);

				// Ignore duplicate
				if ($db->setQuery($query)->execute())
				{

				}

				// Update state
				if (!empty($ids))
				{
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__xgallery_flickr_contact_photos'))
						->set(array($db->quoteName('state') . ' = 3'))
						->where($db->quoteName('pid') . ' IN ( ' . implode(',', $ids) . ' )')
						->where($db->quoteName('state') . ' = 2');

					$db->setQuery($query)->execute();
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query));

			$db->transactionRollback();
		}
	}

	protected function resize($width, $height, $photoDir, $fileName)
	{
		$saveDir       = JPATH_ROOT . '/' . $photoDir . '/' . $width . 'x' . $height;
		$photoFilepath = JPATH_ROOT . '/' . $photoDir . '/' . $fileName;

		$image = new \Eventviva\ImageResize($photoFilepath);

		if (!is_dir($saveDir))
		{
			\Joomla\Filesystem\Folder::create($saveDir);
		}

		$image->crop(1366, 768);
		$image->save($saveDir . '/' . $fileName);
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliPhotosFlickr')->execute();
