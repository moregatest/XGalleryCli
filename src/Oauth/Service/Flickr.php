<?php
/**
 * @package     XGallery.Cli
 * @subpackage  OAuth.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Oauth\Service;

use XGallery\Oauth\Oauth;
use XGallery\System\Configuration;

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
		$config                    = Configuration::getInstance();
		$this->server              = 'Flickr';
		$this->client_id           = $config->get('flickr_client_id');
		$this->client_secret       = $config->get('flickr_client_secret');
		$this->access_token        = $config->get('flickr_access_token');
		$this->access_token_secret = $config->get('access_token_secret');

		// 'read', 'write' or 'delete'
		$this->scope = $config->get('flickr_scope', 'read');

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
	 *
	 * @throws \Exception
	 */
	protected function execute($parameters, $url = self::API_ENDPOINT, $method = 'GET', $options = array())
	{
		$parameters = array_merge($this->defaultParameters, $parameters);
		$options    = array_merge($this->defaultOptions, $options);

		$respond = parent::execute($parameters, $url, $method, $options);

		if ($respond && isset($respond->stat) && $respond->stat == XGALLERY_FLICKR_STAT_SUCCESS)
		{
			return $respond;
		}

		return false;
	}
}
