<?php

require_once __DIR__ . '/../../bootstrap.php';

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.0
 */
class XgalleryCliFlickrDownload extends \Joomla\CMS\Application\CliApplication
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  boolean
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

		$model = \XGallery\Model\Flickr::getInstance();

		if ($pid)
		{
			try
			{
				$db->transactionStart();

				$photo = \XGallery\Model\Flickr::getInstance()->getPhoto($pid);

				if ($photo === null)
				{
					return false;
				}

				$urls = json_decode($photo->urls);
				$size = end($urls->sizes->size);

				if ($size->media == 'photo')
				{
					$toDir = XPATH_MEDIA . $photo->owner;

					if (is_dir($toDir) && !file_exists($toDir))
					{
						\Joomla\Filesystem\Folder::create($toDir);
					}

					$fileName = basename($size->source);
					$saveTo   = $toDir . '/' . $fileName;

					$originalFileSize = \XGallery\Environment\Filesystem\Helper::downloadFile($size->source, $saveTo);
					$downloadedFileSize = filesize($saveTo);

					if ($originalFileSize === false || $originalFileSize != $downloadedFileSize)
					{
						if (file_exists($saveTo))
						{
							\Joomla\Filesystem\File::delete($saveTo);
						}

						throw new Exception('File is not validated: ' . $saveTo);
					}
					else
					{
						$model->updatePhoto($pid, array('state' => XGALLERY_FLICKR_PHOTO_STATE_DOWNLOADED));
					}
				}

				$db->transactionCommit();
			}
			catch (Exception $exception)
			{
				\XGallery\Log\Helper::getLogger()->error(
					$exception->getMessage(),
					array('query' => (string) $db->getQuery(), 'url' => get_object_vars($urls))
				);
				$db->transactionRollback();

				return false;
			}
		}

		$db->disconnect();

		return true;
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
\Joomla\CMS\Application\CliApplication::getInstance('XgalleryCliFlickrDownload')->execute();
