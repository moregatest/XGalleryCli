<?php

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     ${NAMESPACE}
 *
 * @since       2.0.0
 */
class XgalleryFlickr extends XgalleryFlickrBase
{
	/**
	 * @param array $contacts
	 * @param array $params
	 *
	 * @return array
	 *
	 * @since  2.0.0
	 */
	public function getContactsList(&$contacts = array(), $params = array())
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

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
	 * @param array $params
	 *
	 * @return boolean|object
	 *
	 * @since  2.0.0
	 */
	protected function getContacts($params = array())
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		return ($this->execute($params = array_merge(array(
			'method'         => 'flickr.contacts.getList',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'per_page'       => 1000
		), $params)));
	}

	/**
	 * @param       $nsid
	 * @param array $photos
	 * @param array $params
	 *
	 * @return array
	 *
	 * @since  2.0.0
	 */
	public function getPhotosList($nsid, &$photos = array(), $params = array())
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		$return = $this->getPhotos(
			array_merge(
				array(
					'safe_search' => 3,
					'user_id'     => $nsid
				),
				$params
			));

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
	 * @param $params
	 *
	 * @return boolean|object
	 *
	 * @since  2.0.0
	 */
	protected function getPhotos($params)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		return $this->execute(array_merge(array(
			'method'         => 'flickr.people.getPhotos',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'per_page'       => 500
		), $params));
	}

	/**
	 * @param $pid
	 *
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 */
	public function getPhotoSizes($pid)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		return $this->execute(array(
			'method'         => 'flickr.photos.getSizes',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'photo_id'       => $pid
		));
	}

	/**
	 * @param $url
	 *
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 */
	public function lookupUser($url)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		return $this->execute(array(
			'method'         => 'flickr.urls.lookupUser',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'url'            => $url
		));
	}
}
