<?php
/**
 * @package     XGalleryCli.Application
 * @subpackage  Flickr.Download
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
	 * @throws  \Exception
	 */
	protected function doExecute()
	{
		return $this->downloadFromNsid();
	}

	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.1.0
	 *
	 * @throws  \Exception
	 */
	protected function downloadFromNsid()
	{
		$this->log(__FUNCTION__, $this->input->getArray());

		$pid = $this->input->get('pid');

		if (!$pid)
		{
			return false;
		}

		try
		{
			$db = Factory::getDbo();
			$db->transactionStart();

			$photo = $this->getPhoto($pid);

			if (!$photo)
			{
				return false;
			}

			$urls = json_decode($photo->urls);

			if (!$urls)
			{
				return false;
			}

			$size = end($urls->sizes->size);

			switch ($size->media)
			{
				default:
				case 'photo':
					$this->downloadPhoto($photo, $size, $pid);
					break;
			}

			$db->transactionCommit();
			$db->disconnect();
		}
		catch (\Exception $exception)
		{
			$this->logger->error($exception->getMessage(), array('query' => (string) $db->getQuery(), 'url' => get_object_vars($urls)));
			$db->transactionRollback();
			$db->disconnect();

			return false;
		}

		return true;
	}

	/**
	 * @param   object  $photo Photo object
	 * @param   object  $size  Size object
	 * @param   integer $pid   Pid
	 *
	 * @return  boolean
	 * @throws  \Exception
	 *
	 * @since   2.1.0
	 */
	private function downloadPhoto($photo, $size, $pid)
	{
		$toDir = Factory::getConfiguration()->get('flickr_path', XPATH_ROOT . '/media/Flickr') . '/' . $photo->owner;

		Directory::create($toDir);

		$saveTo = $toDir . '/' . basename($size->source);

		// Process download
		$originalFileSize = Helper::downloadFile($size->source, $saveTo);

		if (!File::exists($saveTo))
		{
			throw new \Exception('File download failed: ' . $saveTo);
		}

		if ($originalFileSize === false || $originalFileSize != filesize($saveTo))
		{
			File::delete($saveTo);

			throw new \Exception('File is not validated: ' . $saveTo);
		}

		return $this->getModel()->updatePhoto($pid, array('state' => XGALLERY_FLICKR_PHOTO_STATE_DOWNLOADED));
	}

	/**
	 * @param   string $pid Photo ID
	 *
	 * @return  boolean|object
	 *
	 * @since   2.1.0
	 */
	protected function getPhoto($pid)
	{
		$model = $this->getModel();

		// Get photo from cache
		$cache = Factory::getCache();
		$photo = $cache->getItem('flickr/photo/' . $pid);

		if ($photo->isMiss())
		{
			$this->log('Cache not found', null, 'warning');
			$photo = $model->getPhoto($pid);
		}
		else
		{
			$photo = $photo->get();
			$this->log('Found pid from cache: ', array($photo), 'notice');
		}

		if ($photo === null)
		{
			$this->log('Can not get photo to download from ID: ' . $pid, null, 'notice');

			return false;
		}

		return $photo;
	}
}
