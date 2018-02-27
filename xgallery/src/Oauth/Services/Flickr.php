<?php
/**
 * @package     XGallery.Cli
 * @subpackage  OAuth.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Oauth\Services;

use XGallery\Oauth\Oauth;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Libraries
 *
 * @since       2.0.0
 */
class Flickr extends Oauth
{
	/**
	 * @var   null|\oauth_client_class
	 *
	 * @since  2.0.0
	 */
	protected $client = null;

	/**
	 * @var    array
	 * @since  2.0.0
	 */
	protected $defaultParameters = array('format' => 'json', 'nojsoncallback' => '1');

	/**
	 * @var    array
	 * @since  2.0.0
	 */
	protected $defaultOptions = array('FailOnAccessError' => true);

	CONST API_ENDPOINT = 'https://api.flickr.com/services/rest/';

	/**
	 * XgalleryFlickrBase constructor.
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		$this->server              = 'Flickr';
		$this->redirect_uri        = \Joomla\CMS\Uri\Uri::root() . 'xgallery/cli/xgallery.php';
		$this->client_id           = 'a0b36e86ee8ecb4f992f14b5d00e29a9';
		$this->client_secret       = '4a1647401ff0d777';
		$this->access_token        = '72157675968581360-4aa75c21a7402ce3';
		$this->access_token_secret = '777bd05f9bd4cb00';

		// 'read', 'write' or 'delete'
		$this->scope = 'read';

		parent::__construct();
	}

	/**
	 *
	 * @return static
	 *
	 * @since  2.0.0
	 */
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
	 * @param   array  $parameters Parameters
	 * @param   string $url        URL
	 * @param   string $method     Method
	 * @param   array  $options    Options
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 */
	protected function execute($parameters, $url = self::API_ENDPOINT, $method = 'GET', $options = array())
	{
		$parameters = array_merge($this->defaultParameters, $parameters);
		$options    = array_merge($this->defaultOptions, $options);

		$respond = parent::execute($parameters, $url, $method, $options);

		if ($respond && isset($respond->stat) && $respond->stat == 'ok')
		{
			return $respond;
		}

		return false;
	}
}
