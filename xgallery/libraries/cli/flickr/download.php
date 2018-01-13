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
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		\Joomla\CMS\Factory::$application = $this;

		$input = \Joomla\CMS\Factory::getApplication()->input->cli;

		$db  = \Joomla\CMS\Factory::getDbo();
		$pid = $input->get('pid');

		$model = XgalleryModelFlickr::getInstance();

		if ($pid)
		{
			try
			{
				$db->transactionStart();

				$photo = XgalleryModelFlickr::getInstance()->getFlickrPhoto($pid);

				if ($photo)
				{
					$urls = json_decode($photo->urls);
					$url  = end($urls->sizes->size);

					$toDir = JPATH_ROOT . '/media/xgallery/' . $photo->owner;
					\Joomla\Filesystem\Folder::create($toDir);
					$fileName = basename($url->source);
					$saveTo   = $toDir . '/' . $fileName;

					$originalFileSize = XgalleryHelperFile::downloadFile($url->source, $saveTo);

					if ($originalFileSize === false || $originalFileSize != filesize($saveTo))
					{
						\Joomla\Filesystem\File::delete($saveTo);

						throw new Exception('Download failed');
					}
					else
					{
						$model->updatePhotoState($pid, 2);

						XgalleryHelperLog::getLogger()->info('---- Download completed ' . $pid . ' ----');
					}
				}

				$db->transactionCommit();
			}
			catch (Exception $exception)
			{
				XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $db->getQuery(), 'url' => get_object_vars($urls)));
				$db->transactionRollback();
			}
		}

		$db->disconnect();
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('XgalleryCliFlickrDownload')->execute();
