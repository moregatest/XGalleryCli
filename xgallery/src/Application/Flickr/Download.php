<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use XGallery\Application;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Model\Flickr;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @since       2.0.0
 */
class Download extends Application\Cli
{
	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 * @throws \Exception
	 */
	public function execute()
	{
		$db  = Factory::getDbo();
		$pid = $this->input->get('pid');

		$model = Flickr::getInstance();

		if ($pid)
		{
			try
			{
				$db->transactionStart();
				$photo = Flickr::getInstance()->getPhoto($pid);

				if ($photo === null)
				{
					\XGallery\Log\Helper::getLogger()->notice('Can not get photo to download from ID: ' . $pid);

					return false;
				}

				$urls = json_decode($photo->urls);
				$size = end($urls->sizes->size);

				// Only download photo
				if ($size->media == 'photo')
				{
					$toDir = XPATH_MEDIA . $photo->owner;

					if (!is_dir($toDir))
					{
						Folder::create($toDir);
					}

					$fileName = basename($size->source);
					$saveTo   = $toDir . '/' . $fileName;

					$originalFileSize = Helper::downloadFile($size->source, $saveTo);

					if (file_exists($saveTo))
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
				\XGallery\Log\Helper::getLogger()->error(
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
