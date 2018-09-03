<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Nct;

use XGallery\Application\Nct;
use XGallery\Environment;
use XGallery\Factory;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @since       2.0.0
 */
class Search extends Nct
{
	/**
	 * @return boolean
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since  2.1.0
	 */
	public function doExecute()
	{
		$url   = $this->getSearchUrl();
		$pages = $this->service->getPages($url);

		if ($pages === 0)
		{
			return false;
		}

		// Get a db connection.
		$db = Factory::getDbo();

		for ($page = 1; $page <= $pages; $page++)
		{
			$songs = $this->service->getSongsFromSearchView($url . '&page=' . $page);

			if (empty($songs))
			{
				continue;
			}

			foreach ($songs as $song)
			{
				$data          = new \stdClass;
				$data->name    = $song['name'];
				$data->playUrl = $song['href'];

				try
				{
					$db->insertObject('#__nct_songs', $data, 'id');
				}
				catch (\Exception $exception)
				{
					$this->log($exception->getMessage(), array(), 'error');
				}
			}
		}

		$db->disconnect();

		return parent::doExecute();
	}

	/**
	 * @return string
	 */
	protected function getSearchUrl()
	{
		return $this->service->builderSearchUrl(
			array(
				'title'  => $this->input->get('title'),
				'singer' => $this->input->get('singer'),
				'type'   => $this->input->get('type'),
			)
		);
	}

	/**
	 * @return boolean
	 *
	 * @since  2.1.0
	 * @throws \Exception
	 */
	protected function doAfterExecute()
	{
		$args                = $this->input->getArray();
		$args['application'] = 'Nct.Download';

		Environment::execService($args);

		return parent::doAfterExecute();
	}
}
