<?php

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     ${NAMESPACE}
 *
 * @since       2.0.0
 */
class XgalleryFlickrBase
{
	/**
	 * @var null|oauth_client_class
	 */
	protected $client = null;

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
		$this->client->redirect_uri        = JURI::root() . 'xgallery/cli/xgallery.php';
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

		if ($instance)
		{
			return $instance;
		}

		$instance = new static;

		return $instance;
	}


	/**
	 * @param           $parameters
	 * @param   string  $url
	 * @param   string  $method
	 * @param   array   $options
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 */
	protected function execute($parameters, $url = 'https://api.flickr.com/services/rest/', $method = 'GET', $options = array('FailOnAccessError' => true))
	{
		XgalleryHelperLog::getLogger()->info(__FUNCTION__, func_get_args());
		$id = md5(serialize(func_get_args()));

		$item = XgalleryHelperCache::getItem('flickr/' . $id);

		if (!$item->isMiss())
		{
			XgalleryHelperLog::getLogger()->info('Has cached');

			return $item->get();
		}

		XgalleryHelperLog::getLogger()->info('Has no cache');

		$return = $this->client->CallAPI($url, $method, $parameters, $options, $respond);
		$item->set(false);
		XgalleryHelperCache::save($item);

		if (!$return)
		{
			return false;
		}

		if ($respond && isset($respond->stat) && $respond->stat == 'ok')
		{
			return $respond;
		}

		return false;
	}
}