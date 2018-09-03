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
use XGallery\Factory;

/**
 * Class Playlist
 * @package XGallery\Application\Nct
 *
 * @since   2.1.1
 */
class Playlist extends Nct
{
	/**
	 * @return boolean|void
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function doExecute()
	{
		$this->getSongsFromPlaylist();
	}

	/**
	 * @return boolean
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	protected function getSongsFromPlaylist()
	{
		$songs = $this->service->getSongsFromPlaylist($this->input->get('url'));

		if (empty($songs))
		{
			return false;
		}

		// Get a db connection.
		$db = Factory::getDbo();

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
}