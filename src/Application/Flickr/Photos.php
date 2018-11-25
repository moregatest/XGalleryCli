<?php
/**
 * @package     XGalleryCli.Application
 * @subpackage  Flickr.Photos
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

defined('_XEXEC') or die;

use Exception;
use XGallery\Application;
use XGallery\Environment;
use XGallery\Factory;

/**
 * @package     XGallery.Application
 * @subpackage  Flickr.Photos
 *
 * @since       2.0.0
 */
class Photos extends Application\Flickr
{
	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws Exception
	 */
	protected function doExecute()
	{
		$nsid = $this->getNsid();

		// Download unfinished photos of request nsid;
		// Get photo sizes of current contact
		$photos = $this->getModel()->getPhotos($nsid, $this->getLimit(), 0, XGALLERY_FLICKR_PHOTO_STATE_SIZED);

		if (Environment::isCli())
		{
			echo $nsid . ':' . count($photos) . PHP_EOL;
		}

		$this->log('Download sized photos: ' . count($photos));

		if (!empty($photos))
		{
			foreach ($photos as $photo)
			{
				$this->downloadPhoto($photo);
			}
		}

		if ($this->insertPhotos($nsid))
		{
			return parent::doExecute();
		}

		return false;
	}

	/**
	 * @return  boolean|string
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	private function getNsid()
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__);

		// Custom args
		$url  = $this->input->get('url', null, 'RAW');
		$nsid = $this->input->get('nsid', null);

		// Get nsid from URL
		if ($url)
		{
			$nsid = $this->service->urls->lookupUser($url);

			if ($nsid)
			{
				$nsid = $nsid->user->id;
			}
		}

		if ($nsid === null)
		{
			$model = $this->getModel();
			$nsid  = $model->getContact();

			// Update contact immediately to prevent another step over
			$model->updateContact($nsid);
		}

		$this->input->set('nsid', $nsid);

		return $nsid;
	}

	/**
	 * Get photos from Nsid and insert into database
	 *
	 * @param   string $nsid Flickr Nsid
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function insertPhotos($nsid)
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			$this->log('No nsid provided', null, 'warning');

			return false;
		}

		$model = $this->getModel();

		// Fetch photos
		$photos = $this->service->getPhotosList($nsid);

		$this->log('Photos: ' . count($photos));

		// Insert photos into database
		return $model->insertPhotos($photos);
	}

	/**
	 * @param   string $nsid Nsid
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	protected function downloadPhotos($nsid)
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		$model = $this->getModel();

		// Only process if this user have any photos
		try
		{
			// Get Flickr download limit. By default use maxConnection
			$limit = $this->getLimit();

			// Get photo sizes of current contact with pending status
			$photos = $model->getPhotos($nsid, $limit, 0);

			if (!empty($photos))
			{
				$this->log('There is no photos for getting sizes and download', null, 'notice');

				return false;
			}

			// Process download photos
			foreach ($photos as $photo)
			{
				$sized = $this->service->photos->getPhotoSizes($photo->id);

				if (!$sized)
				{
					continue;
				}

				$sized = json_encode($sized);

				// Update sized
				$model->updatePhoto($photo->id, array('urls' => $sized, 'state' => XGALLERY_FLICKR_PHOTO_STATE_SIZED));

				$photo->urls  = $sized;
				$photo->state = XGALLERY_FLICKR_PHOTO_STATE_SIZED;

				$this->downloadPhoto($photo);
			}

			return true;
		}
		catch (Exception $exception)
		{
			$this->log($exception->getMessage(), null, 'error');

			return false;
		}
	}

	/**
	 * @param   object $photo Photo
	 *
	 * @return  void
	 * @throws  Exception
	 */
	protected function downloadPhoto($photo)
	{
		$cache = Factory::getCache();
		$item  = $cache->getItem('flickr/photo/' . $photo->id);

		// Save this photo with sized to cache then we can re-use without query
		$item->set($photo);
		$cache->saveWithExpires($item);

		$this->execService(
			'Download',
			array(
				'pid' => $photo->id
			)
		);
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws Exception
	 */
	protected function doAfterExecute()
	{
		parent::doAfterExecute();

		// Download photos from this nsid
		return $this->downloadPhotos($this->input->get('nsid'));
	}

	/**
	 * @return integer|mixed
	 */
	private function getLimit()
	{
		// Get Flickr download limit. By default use maxConnection
		$limit = $this->get('flickr_download_limit');

		if (!$limit)
		{
			$limit = (int) $this->getModel()->getMaxConnection()->Value - 10;
			$this->set('flickr_download_limit', $limit);
		}

		return $limit;
	}
}
