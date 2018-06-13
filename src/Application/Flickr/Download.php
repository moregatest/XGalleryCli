<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

defined('_XEXEC') or die;

use XGallery\Application;
use XGallery\Environment\Filesystem\Directory;
use XGallery\Environment\Filesystem\File;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Factory;

/**
 * @package     XGallery.Application
 * @subpackage  Flickr.Download
 *
 * @since       2.0.0
 */
class Download extends Application\Flickr
{
	/**
	 * @return  boolean
	 *
	 * @since   2.1.0
	 *
	 * @throws \Exception
	 */
	protected function doExecute()
	{
		return $this->downloadFromNsid();
	}

	/**
	 *
	 * @return boolean
	 *
	 * @since   2.1.0
	 *
	 * @throws \Exception
	 */
	protected function downloadFromNsid()
	{
		$this->log(__FUNCTION__, $this->input->getArray());

		$db  = Factory::getDbo();
		$pid = $this->input->get('pid');

		if ($pid)
		{
			try
			{
				$db->transactionStart();

				$model = $this->getModel();

				// Get photo from cache
				$photo = \XGallery\Cache\Helper::getItem('flickr/photo/' . $pid);

				if ($photo->isMiss())
				{
					// If cache expired then we do query into datbase
					$photo = $model->getPhoto($pid);
				}
				else
				{
					$photo = $photo->get();
				}

				if ($photo === null)
				{
					$this->log('Can not get photo to download from ID: ' . $pid, null, 'notice');

					return false;
				}

				$urls = json_decode($photo->urls);
				$size = end($urls->sizes->size);

				// Only download photo
				if ($size->media == 'photo')
				{
					$toDir = Factory::getConfiguration()->get('media_dir') . '/' . $photo->owner;

					Directory::create($toDir);

					$fileName = basename($size->source);
					$saveTo   = $toDir . '/' . $fileName;

					// Process download
					$originalFileSize = Helper::downloadFile($size->source, $saveTo);

					if (File::exists($saveTo))
					{
						if ($originalFileSize === false || $originalFileSize != filesize($saveTo))
						{
							File::delete($saveTo);

							throw new \Exception('File is not validated: ' . $saveTo);
						}
						else
						{
							$model->updatePhoto($pid, array('state' => XGALLERY_FLICKR_PHOTO_STATE_DOWNLOADED));
						}
					}
					else
					{
						throw new \Exception('File download failed: ' . $saveTo);
					}
				}

				$db->transactionCommit();
			}
			catch (\Exception $exception)
			{
				$this->logger->error(
					$exception->getMessage(),
					array('query' => (string) $db->getQuery(), 'url' => get_object_vars($urls))
				);
				$db->transactionRollback();
			}
		}

		$db->disconnect();

		return true;
	}
}
