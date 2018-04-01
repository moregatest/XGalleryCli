<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Flickr;

use XGallery\Application;
use XGallery\Cache\Helper;
use XGallery\Factory;
use XGallery\System\Configuration;

defined('_XEXEC') or die;


/**
 * @package     XGallery.Application
 * @subpackage  Flickr.Photos
 *
 * @since       2.0.0
 */
class Photos extends Application\Flickr
{
	/**
	 * Entry point
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function execute()
	{
		return $this->insertPhotosFromFlickr($this->getNsid());
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
		Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$model = $this->getModel();

		// Custom args
		$url  = $this->input->get('url', null, 'RAW');
		$nsid = $this->input->get('nsid', null);

		// Get nsid from URL
		if ($url)
		{
			$nsid = Factory::getService('Flickr')->lookupUser($url);

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
		Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			Factory::getLogger()->warning('No nsid provided');

			return false;
		}

		// Update contact to prevent another thread step over it
		$model = $this->getModel();
		$model->updateContact($nsid);

		// Fetch photos
		$photos = Factory::getService('Flickr')->getPhotosList($nsid);

		Factory::getLogger()->info('Photos: ' . count($photos));

		// Insert photos into database
		$model->insertPhotos($photos);

		// Download photos from this nsid
		$this->downloadPhotos($nsid);

		return true;
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
		Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$model  = $this->getModel();
		$config = Configuration::getInstance();

		// Only process if this user have any photos
		try
		{
			// Get Flickr download limit. By default use maxConnection
			$limit = $config->get('flickr_download_limit');

			if (!$limit)
			{
				$limit = (int) $model->getMaxConnection()->Value - 10;
				$config->set('flickr_download_limit', $limit);
				$config->save();
			}

			// Get photo sizes of current contact
			$photos = $model->getPhotos($nsid, $limit, 0);

			if (!$photos || empty($photos))
			{
				return false;
			}

			// Process download photos
			foreach ($photos as $photo)
			{
				$sized = Factory::getService('Flickr')->getPhotoSizes($photo->id);

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
				$item         = Helper::getItem('flickr/photo/' . $photo->id);
				$item->set($photo);

				// Save this photo with sized to cache then we can re-use without query
				Helper::save($item);

				\XGallery\Environment\Helper::execService($args);
			}

			return true;
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage());

			return false;
		}
	}
}
