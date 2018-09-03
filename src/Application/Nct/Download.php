<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\Nct;

use Joomla\Filesystem\Folder;
use XGallery\Application\Nct;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Factory;
use XGallery\Model;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @since       2.0.0
 */
class Download extends Nct
{
	/**
	 * @return boolean
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since  2.0.0
	 */
	public function doExecute()
	{
		$model = Model::getInstance('Nct');
		$songs = $model->getSongs($this->input->getInt('id'));

		foreach ($songs as $index => $song)
		{
			$this->download($song);
		}

		return parent::doExecute();
	}

	/**
	 * @param   object $song Song object
	 *
	 * @return  boolean
	 * @throws  \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since   2.1.0
	 */
	private function download($song)
	{
		if (!is_object($song))
		{
			return false;
		}

		$db = Factory::getDbo();

		$songData = $this->service->getData($song->playUrl);

		if (!filter_var($songData['flashlink'], FILTER_VALIDATE_URL))
		{
			return true;
		}

		$downloadLink = trim($this->service->getDownloadLink($songData['flashlink']));

		$fileName = explode('?', basename($downloadLink));
		$fileName = $fileName[0];

		$toDir = Factory::getConfiguration()->get('nct_path', XPATH_ROOT . '/NCT/') . '/' . $songData['singer'];

		if (!is_dir($toDir))
		{
			Folder::create($toDir);
		}

		$saveTo = $toDir . '/' . $fileName;

		try
		{
			if (!Helper::downloadFile($downloadLink, $saveTo))
			{
				return false;
			}
		}
		catch (\Exception $exception)
		{
			return false;
		}

		$song->singer   = $songData['singer'];
		$song->flashUrl = $songData['flashlink'];
		$song->state    = 1;

		if (!$db->updateObject('#__nct_songs', $song, array('id')))
		{
			$db->disconnect();

			return false;
		}

		$db->disconnect();

		return true;
	}
}
