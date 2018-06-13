<?php
/**
 * @package     XGallery.Cli
 * @subpackage  OAuth
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Oauth;

use XGallery\Cache\Helper;
use XGallery\Factory;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  OAuth
 *
 * @since       2.0.0
 */
class Oauth extends \oauth_client_class
{
	/**
	 * @var \Monolog\Logger|null
	 */
	protected $logger = null;

	/**
	 * Oauth constructor.
	 *
	 * @since       2.0.0
	 *
	 * @throws \Exception
	 */
	public function __construct()
	{
		$this->configuration_file = XPATH_3RD . '/oauth-api/oauth_configuration.json';
		$this->offline            = true;
		$this->debug              = false;
		$this->debug_http         = false;

		if ($success = $this->Initialize())
		{
			$this->Finalize($success);
		}

		$this->logger = Factory::getLogger(get_class($this));
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
	protected function execute($parameters, $url, $method = 'GET', $options = array())
	{
		$this->logger->info(__FUNCTION__, $parameters);

		$id = md5($url . md5(serialize(func_get_args())));

		$cache = Factory::getCache();
		$item  = $cache->getItem($id);

		if (!$item->isMiss())
		{
			$this->logger->notice('Oauth request has cached');

			return $item->get();
		}

		$startTime = microtime(true);
		$return    = $this->CallAPI($url, $method, $parameters, $options, $respond);

		$endTime     = microtime(true);
		$executeTime = $endTime - $startTime;

		$this->logger->debug('Oauth executed time: ' . $executeTime, array($return));

		$item->set($respond);
		$cache->saveWithExpires($item);

		if (!$return)
		{
			return false;
		}

		return $respond;
	}
}
