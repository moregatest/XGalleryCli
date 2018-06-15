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

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @since       2.0.0
 */
class Song extends Cli
{
	/**
	 * @return boolean|void
	 *
	 * @since  2.0.0
	 * @throws \Exception
	 */
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

			$toDir = Factory::getConfiguration()->get('media_dir', XPATH_ROOT . '/NCT');

			if (!is_dir($toDir))
			{
				Folder::create($toDir);
			}

			$saveTo = $toDir . '/' . $fileName;

			Helper::downloadFile($downloadLink, $saveTo);
		}
	}
}
