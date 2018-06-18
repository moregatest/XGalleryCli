<?php
/**
 * @package     XGallery.Service
 * @subpackage  Nct
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Service;

use GuzzleHttp\Client;
use Joomla\Uri\Uri;
use Symfony\Component\DomCrawler\Crawler;

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
	 * @var Client
	 */
	protected $client;

	/**
	 * Nct constructor.
	 *
	 * @since   2.1.0
	 */
	public function __construct()
	{
		$this->client = new Client;
	}

	/**
	 * @param   string $filter Query filter
	 *
	 * @return  mixed
	 * @throws  \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since   2.1.0
	 */
	public function getPages($filter)
	{
		$respond = $this->client->request('GET', 'https://www.nhaccuatui.com/tim-nang-cao?' . $filter);
		$crawler = new Crawler($respond->getBody()->getContents());

		$uri = new Uri($crawler->filter('div.box_pageview a')->last()->attr('href'));

		return $uri->getVar('page');
	}

	/**
	 * @param   string $url Songs URL
	 *
	 * @return  array
	 * @throws  \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since   2.1.0
	 */
	public function getSongs($page)
	{
		$songs = array();

		$respond = $this->client->request('GET', 'https://www.nhaccuatui.com/tim-nang-cao?page=' . $page);
		$html    = $respond->getBody();

		if (!$html)
		{
			return $songs;
		}

		$crawler = new Crawler($html->getContents());

		foreach ($crawler->filter('ul.search_returns_list li.list_song div.item_content a.name_song') as $node)
		{
			$song['name'] = $node->nodeValue;
			$song['href'] = $node->getAttribute('href');
			$songs[]      = $song;
		}

		return $songs;
	}

	/**
	 * @param   string $url Url
	 *
	 * @return  boolean|string
	 * @throws  \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since   2.0.0
	 */
	public function getData($url)
	{
		$respond = $this->client->request('GET', $url);
		$html    = $respond->getBody()->getContents();

		$crawler = new Crawler($html);

		$data = array();

		foreach ($crawler->filter('#box_playing_id > div.info_name_songmv > div.name_title > h1') as $node)
		{
			$data['name'] = $node->nodeValue;
		}

		foreach ($crawler->filter('#box_playing_id > div.info_name_songmv > div.name_title > h2 > a') as $node)
		{
			$data['singer'] = $node->nodeValue;
		}

		$start = strpos($html, 'https://www.nhaccuatui.com/flash/xml?html5=true&key1=');
		$end   = strpos($html, '"', $start);

		$data['flashlink'] = substr($html, $start, $end - $start);

		return $data;
	}

	/**
	 * @param   string $url Url
	 *
	 * @return  string
	 * @throws  \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since   2.0.0
	 */
	public function getDownloadLink($url)
	{
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$xml     = $respond->getBody()->getContents();
		$dom     = simplexml_load_string($xml);

		return (string) $dom->track->location;
	}

	public function builderSearchUrl($condition = array())
	{
		$baseUrl = 'https://www.nhaccuatui.com/tim-nang-cao';

		return $baseUrl . '?' . http_build_query($condition);
	}
}
