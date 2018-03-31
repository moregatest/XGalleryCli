<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\NCT;

use GuzzleHttp\Client;
use Joomla\Uri\Uri;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Application\Cli;
use XGallery\Environment\Helper;
use XGallery\Factory;
use XGallery\Service\Nct;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @since       2.0.0
 */
class Search extends Cli
{
	/**
	 * @return boolean
	 *
	 * @since  2.0.0
	 *
	 * @throws \Exception
	 */
	public function execute()
	{
		$pages = $this->getPages();

		for ($page = 1; $page <= $pages; $page++)
		{
			$url = 'https://www.nhaccuatui.com/tim-nang-cao?title=&user=&singer=Ho+Quang+Hieu&kbit=&type=1&sort=&direction=2&page=' . $page;
			$this->getSongs($url);
		}

		return true;
	}

	/**
	 * @return array
	 *
	 * @since  2.0.0
	 */
	public function getPages()
	{
		$client  = new Client;
		$respond = $client->request('GET', 'https://www.nhaccuatui.com/tim-nang-cao?title=&user=&singer=Ho+Quang+Hieu&kbit=&type=1&sort=&direction=2');
		$html    = $respond->getBody();
		$crawler = new Crawler($html->getContents());

		$href = $crawler->filter('div.box_pageview a')->last()->attr('href');
		$uri  = new Uri($href);

		return $uri->getVar('page');
	}

	/**
	 * @param   string $url Url
	 *
	 * @return array
	 *
	 * @since  2.0.0
	 *
	 * @throws \Exception
	 */
	public function getSongs($url)
	{
		$songs   = array();
		$client  = new Client;
		$respond = $client->request('GET', $url);
		$html    = $respond->getBody();
		$crawler = new Crawler($html->getContents());

		$service = new Nct;

		foreach ($crawler->filter('ul.search_returns_list li.list_song div.item_content a.name_song') as $node)
		{
			$href    = $node->getAttribute('href');
			$songs[] = $href;

			$flashLink = $service->getFlashLink($href);

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->insert('#__xgallery_nct_songs')
				->columns(array('play_url', 'flash_url'))
				->values(implode(',', array($db->quote($href), $db->quote($flashLink))));

			$db->setQuery($query)->execute();

			$args                = array();
			$args['application'] = 'NCT.Song';
			$args['id']          = $db->insertid();

			Helper::execService($args);
		}

		return $songs;
	}

	/**
	 * @param   string $url Url
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getDownloadLink($url)
	{
		$service = new Nct;

		return $service->getDownloadLink($url);
	}
}
