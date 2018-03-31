<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service;

use GuzzleHttp\Client;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Service
 *
 * @since       2.0.0
 */
class Nct
{
	/**
	 * @param   string $url Url
	 *
	 * @return boolean|string
	 *
	 * @since  2.0.0
	 */
	public function getFlashLink($url)
	{
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$html    = $respond->getBody()->getContents();

		$start = strpos($html, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
		$end   = strpos($html, '"', $start);

		$flashLink = substr($html, $start, $end - $start);

		return $flashLink;
	}

	/**
	 * @param   string $url Url
	 *
	 * @return string
	 *
	 * @since  2.0.0
	 */
	public function getDownloadLink($url)
	{
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$xml     = $respond->getBody()->getContents();
		$dom     = simplexml_load_string($xml);

		return (string) $dom->track->location;
	}
}
