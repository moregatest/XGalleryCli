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

		XgalleryHelperLog::getLogger()->info('Total photos: ' . count($photos), $params);

		return $photos;
	}

	/**
	 * @param $params
	 *
	 * @return bool
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

	protected function execute($parameters, $url = 'https://api.flickr.com/services/rest/', $method = 'GET', $options = array('FailOnAccessError' => true))
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__, func_get_args());

		$driver = new Stash\Driver\FileSystem(array('path' => JPATH_ROOT . '/cache'));

		// Inject the driver into a new Pool object.
		$pool = new Stash\Pool($driver);
		$id   = md5(serialize(func_get_args()));

		$item = $pool->getItem('flickr/' . $id);

		if (!$item->isMiss())
		{
			XgalleryHelperLog::getLogger()->info('Has cached');

			return $item->get();
		}

		$return = $this->client->CallAPI($url, $method, $parameters, $options, $respond);

		if (!$return)
		{
			$item->set(false);
			$pool->save($item);

			return false;
		}

		if ($respond && isset($respond->stat) && $respond->stat == 'ok')
		{
			$item->set($respond);
			$pool->save($item);

			return $respond;
		}

		$item->set(false);
		$pool->save($item);

		return false;
	}
}
