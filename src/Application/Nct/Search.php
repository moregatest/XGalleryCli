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
use XGallery\Environment\Helper;
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
		$filter = $this->input->get('filter');
		$pages  = $this->service->getPages($filter);

		// Get a db connection.
		$db = Factory::getDbo();

		for ($page = 1; $page <= $pages; $page++)
		{
			$songs = $this->service->getSongs($page);

			foreach ($songs as $song)
			{
				$data            = new \stdClass;
				$data->song_name = $song['name'];
				$data->play_url  = $song['href'];

				try
				{
					$db->insertObject('#__nct_songs', $data);
				}
				catch (\Exception $exception)
				{
				}
			}
		}

		return parent::doExecute();
	}
}
