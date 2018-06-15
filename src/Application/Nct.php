<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Nct
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use GuzzleHttp\Client;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Symfony\Component\DomCrawler\Crawler;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Factory;


/**
 * Class Nct
 * @package XGallery\Application
 *
 * @since   2.1.0
 */
class Nct extends Cli
{
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var \XGallery\Service\Nc
	 */
	protected $service;


	public function __construct(Registry $config = null)
	{
		parent::__construct($config);

		$this->client  = new Client;
		$this->service = new \XGallery\Service\Nct;
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
	public function getSongs($url)
	{
		$songs = array();

		$respond = $this->client->request('GET', $url);
		$html    = $respond->getBody();
		$crawler = new Crawler($html->getContents());

		foreach ($crawler->filter('ul.search_returns_list li.list_song div.item_content a.name_song') as $node)
		{
			$flashLink    = $this->service->getFlashLink($node->getAttribute('href'));
			$downloadLink = trim($this->service->getDownloadLink($flashLink));
			$fileName     = explode('?', basename($downloadLink));
			$fileName     = $fileName[0];


			$toDir = Factory::getConfiguration()->get('media_dir', XPATH_ROOT . '/NCT');

			if (!is_dir($toDir))
			{
				Folder::create($toDir);
			}

			$saveTo = $toDir . '/' . $fileName;
			Helper::downloadFile($downloadLink, $saveTo);
		}

		return $songs;
	}
}
