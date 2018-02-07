<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Libraries
 *
 * @since       2.0.0
 */
class XgalleryFlickr extends XgalleryFlickrBase
{
	/**
	 * @param   array $contacts Contacts
	 * @param   array $params   Params
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public function getContactsList(&$contacts = array(), $params = array())
	{
		$return = $this->getContacts($params);

		if ($return)
		{
			$contacts = array_merge($contacts, $return->contacts->contact);

			if ($return->contacts->pages > $return->contacts->page)
			{
				$this->getContactsList($contacts, array('page' => (int) $return->contacts->page + 1));
			}
		}

		XgalleryHelperLog::getLogger()->info('Contacts: ' . count($contacts));

		return $contacts;
	}

	/**
	 * @param   array $params Parameters
	 *
	 * @return boolean|object
	 *
	 * @since  2.0.0
	 */
	protected function getContacts($params = array())
	{
		return ($this->execute(
			array_merge(
				array('method' => 'flickr.contacts.getList', 'per_page' => XGALLERY_FLICKR_CONTACTS_PERPAGE),
				$params
			)
		));
	}

	/**
	 * @param   string $nsid   Nsid
	 * @param   array  $photos Photo
	 * @param   array  $params Parameters
	 *
	 * @return array
	 *
	 * @since  2.0.0
	 */
	public function getPhotosList($nsid, &$photos = array(), $params = array())
	{
		$return = $this->getPhotos(
			array_merge(
				array(
					'safe_search' => 3,
					'user_id'     => $nsid
				),
				$params
			)
		);

		if ($return)
		{
			$photos = array_merge($photos, $return->photos->photo);

			if ($return->photos->pages > $return->photos->page)
			{
				$this->getPhotosList($nsid, $photos, array('page' => (int) $return->photos->page + 1));
			}
		}

		XgalleryHelperLog::getLogger()->info('Photos: ' . count($photos), $params);

		return $photos;
	}

	/**
	 * @param   array $params Parameters
	 *
	 * @return  boolean|object
	 *
	 * @since   2.0.0
	 */
	protected function getPhotos($params)
	{
		return $this->execute(
			array_merge(
				array(
					'method'   => 'flickr.people.getPhotos',
					'per_page' => 500
				), $params
			)
		);
	}

	/**
	 * @param   string $pid Pid
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 */
	public function getPhotoSizes($pid)
	{
		if (empty($pid))
		{
			return false;
		}

		return $this->execute(array(
				'method'   => 'flickr.photos.getSizes',
				'photo_id' => $pid
			)
		);
	}

	/**
	 * @param   string $url Url
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 */
	public function lookupUser($url)
	{
		if (empty($url))
		{
			return false;
		}

		return $this->execute(array(
				'method' => 'flickr.urls.lookupUser',
				'url'    => $url
			)
		);
	}

	/**
	 * @param   string $nsid User id
	 *
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 */
	public function getFavortiesList($nsid = null)
	{
		return $this->execute(array(
				'method'   => 'flickr.favorites.getList',
				'user_id'  => $nsid,
				'per_page' => 500
			)
		);
	}
}
