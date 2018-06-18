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
	 * @return  void
	 * @since  2.1.0
	 */
	protected function cleanup()
	{
		$this->set('nsid', null);

		parent::cleanup();
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doExecute()
	{
		return $this->insertPhotosFromFlickr($this->getNsid());
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doAfterExecute()
	{
		parent::doAfterExecute();

		// Download photos from this nsid
		return $this->downloadPhotos($this->get('nsid'));
	}

	/**
	 * Get photos from Nsid and insert into database
	 *
	 * @param   string $nsid Flickr Nsid
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws  \Exception
	 */
	protected function insertPhotosFromFlickr($nsid)
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			$this->log('No nsid provided', null, 'warning');

			return false;
		}

		// Update contact to prevent another thread step over it
		$model = $this->getModel();
		$model->updateContact($nsid);

		// Fetch photos
		$photos = $this->service->people->getPhotosList($nsid);

		$this->log('Photos: ' . count($photos));
		$this->set('nsid', $nsid);

		// Insert photos into database
		return $model->insertPhotos($photos);
	}

	/**
	 * @param   string $nsid Nsid
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	protected function downloadPhotos($nsid)
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		$model = $this->getModel();

		// Only process if this user have any photos
		try
		{
			// Get Flickr download limit. By default use maxConnection
			$limit = $this->get('flickr_download_limit');

			if (!$limit)
			{
				$limit = (int) $model->getMaxConnection()->Value - 10;
				$this->set('flickr_download_limit', $limit);
			}

			$this->log('Download limit: ' . $limit);

			// Get photo sizes of current contact
			$photos = $model->getPhotos($nsid, $limit, 0);

			if (!$photos || empty($photos))
			{
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

				$args                = $this->input->getArray();
				$args['application'] = 'Flickr.Download';
				$args['pid']         = $photo->id;

				$photo->urls  = $sized;
				$photo->state = XGALLERY_FLICKR_PHOTO_STATE_SIZED;
				$cache        = Factory::getCache();
				$item         = $cache->getItem('flickr/photo/' . $photo->id);

				// Save this photo with sized to cache then we can re-use without query
				$item->set($photo);
				$cache->saveWithExpires($item);

				Environment::execService($args);

				return true;
			}

			return true;
		}
		catch (\Exception $exception)
		{
			$this->log($exception->getMessage(), null, 'error');

			return false;
		}
	}

	/**
	 * @return  boolean|string
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	private function getNsid()
	{
		$this->log(__CLASS__ . '.' . __FUNCTION__);

		$model = $this->getModel();

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
			$nsid = $model->getContact();
		}

		return $nsid;
	}
}
