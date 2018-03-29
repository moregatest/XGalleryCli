<?php
/**
 * Created by PhpStorm.
 * User: soulevilx
 * Date: 3/29/18
 * Time: 2:55 PM
 */

namespace XGallery\Application\NCT;


use Joomla\Filesystem\Folder;
use XGallery\Application\Cli;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Factory;
use XGallery\Service\Nct;

class Song extends Cli
{
	public function execute()
	{
		$id = $this->input->getInt('id');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__xgallery_nct_songs'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$song = $db->setQuery($query)->loadObject();

		if ($song)
		{
			$service      = new Nct;
			$downloadLink = trim($service->getDownloadLink($song->flash_url));
			$fileName     = explode('?', basename($downloadLink));
			$fileName     = $fileName[0];

			$toDir = XPATH_MEDIA . 'NCT';

			if (!is_dir($toDir))
			{
				Folder::create($toDir);
			}

			$saveTo = $toDir . '/' . $fileName;

			Helper::downloadFile($downloadLink, $saveTo);

		}
	}
}