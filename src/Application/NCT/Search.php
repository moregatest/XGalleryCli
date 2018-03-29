<?php
/**
 * Created by PhpStorm.
 * User: soulevilx
 * Date: 3/29/18
 * Time: 2:42 PM
 */

namespace XGallery\Application\NCT;

use GuzzleHttp\Client;
use Joomla\Uri\Uri;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Application\Cli;
use XGallery\Environment\Helper;
use XGallery\Factory;
use XGallery\Service\Nct;

class Search extends Cli
{
	public function execute()
	{
		$pages = $this->getPages();

		for ($page = 1; $page <= $pages; $page++)
		{
			$url = 'https://www.nhaccuatui.com/tim-nang-cao?title=&user=&singer=Ho+Quang+Hieu&kbit=&type=1&sort=&direction=2&page=' . $page;
			$this->getSongs($url);
		}
	}

	public function getPages()
	{
		$client  = new Client();
		$respond = $client->request('GET', 'https://www.nhaccuatui.com/tim-nang-cao?title=&user=&singer=Ho+Quang+Hieu&kbit=&type=1&sort=&direction=2');
		$html    = $respond->getBody();
		$crawler = new Crawler($html->getContents());

		$href = $crawler->filter('div.box_pageview a')->last()->attr('href');
		$uri  = new Uri($href);

		return $uri->getVar('page');
	}

	public function getSongs($url)
	{
		$songs   = array();
		$client  = new Client();
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

	public function getDownloadLink($url)
	{
		$service = new Nct();

		return $service->getDownloadLink($url);
	}
}