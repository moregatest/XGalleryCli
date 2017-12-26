<?php

/**
 * @package     ${NAMESPACE}
 *
 * @since       2.0.0
 */
class XgalleryFlickr
{
	/**
	 * @var null|oauth_client_class
	 */
	private $client = null;

	/**
	 * Xgallery constructor.
	 */
	public function __construct()
	{
		$this->client                      = new oauth_client_class;
		$this->client->configuration_file  = XPATH_LIBRARIES . '/vendor/oauth-api/oauth_configuration.json';
		$this->client->offline             = true;
		$this->client->debug               = false;
		$this->client->debug_http          = false;
		$this->client->server              = 'Flickr';
		$this->client->redirect_uri        = 'http://localhost/xgallery/cli/xgallery.php';
		$this->client->client_id           = 'a0b36e86ee8ecb4f992f14b5d00e29a9';
		$this->client->client_secret       = '4a1647401ff0d777';
		$this->client->access_token        = '72157675968581360-4aa75c21a7402ce3';
		$this->client->access_token_secret = '777bd05f9bd4cb00';

		$this->client->scope = 'read'; // 'read', 'write' or 'delete'

		if (($success = $this->client->Initialize()))
		{
			$this->client->Finalize($success);
		}
	}

	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{
			$instance = new static;
		}

		return $instance;
	}
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

		if ($return && $return->stat == 'ok')
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
	 */
	protected function getContacts($params = array())
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		$params = array_merge(array(
			'method'         => 'flickr.contacts.getList',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'per_page'       => 1000
		), $params);

		$return = $this->client->CallAPI(
			'https://api.flickr.com/services/rest/',
			'GET',
			$params,
			array('FailOnAccessError' => true),
			$result
		);

		if (!$return)
		{
			return false;
		}

		if ($result->stat != 'ok')
		{
			return false;
		}

		return $result;
	}

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

		if ($return && $return->stat == 'ok')
		{
			$photos = array_merge($photos, $return->photos->photo);

			if ($return->photos->pages > $return->photos->page)
			{
				$this->getPhotosList($nsid, $photos, array('page' => (int) $return->photos->page + 1));
			}
		}

		XgalleryHelperLog::getLogger()->info('Total photos: ' . count($photos), $params);

		return $photos;
	}

	protected function getPhotos($params)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		$params = array_merge(array(
			'method'         => 'flickr.people.getPhotos',
			'format'         => 'json',
			'nojsoncallback' => '1',
			'per_page'       => 500
		), $params);

		$return = $this->client->CallAPI(
			'https://api.flickr.com/services/rest/',
			'GET',
			$params,
			array('FailOnAccessError' => true),
			$result
		);

		if (!$return)
		{
			return false;
		}

		if ($result->stat != 'ok')
		{
			return false;
		}

		return $result;
	}

	public function getPhotoSizes($pid)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		$return = $this->client->CallAPI(
			'https://api.flickr.com/services/rest/',
			'GET',
			array(
				'method'         => 'flickr.photos.getSizes',
				'format'         => 'json',
				'nojsoncallback' => '1',
				'photo_id'       => $pid
			),
			array('FailOnAccessError' => true),
			$result
		);

		if (!$return)
		{
			return false;
		}

		if ($result->stat != 'ok')
		{
			return false;
		}

		return $result;
	}

	public function lookupUser($url)
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__);

		$return = $this->client->CallAPI(
			'https://api.flickr.com/services/rest/',
			'GET',
			array(
				'method'         => 'flickr.urls.lookupUser',
				'format'         => 'json',
				'nojsoncallback' => '1',
				'url'            => $url
			),
			array('FailOnAccessError' => true),
			$result
		);

		if (!$return)
		{
			return false;
		}

		if ($result->stat != 'ok')
		{
			return false;
		}

		return $result;
	}
}
