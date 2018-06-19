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
		$url = $this->service->builderSearchUrl(
			array(
				'title' => $this->input->get('title'),
				'singer'  => $this->input->get('singer'),
				'type'    => $this->input->get('type'),
			)
		);

		$pages = $this->service->getPages($url);

		// Get a db connection.
		$db = Factory::getDbo();

		for ($page = 1; $page <= $pages; $page++)
		{
			$songs = $this->service->getSongs($url . '&page=' . $page);

			foreach ($songs as $song)
			{
				$data            = new \stdClass;
				$data->song_name = $song['name'];
				$data->play_url  = $song['href'];

				try
				{
					$db->insertObject('#__nct_songs', $data, 'id');

					$args                = $this->input->getArray();
					$args['application'] = 'Nct.Download';
					$args['id']          = $data->id;

					Environment::execService();
				}
				catch (\Exception $exception)
				{
				}
			}
		}

		return parent::doExecute();
	}
}
