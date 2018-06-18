<?php
/**
 * Created by PhpStorm.
 * User: soulevilx
 * Date: 3/29/18
 * Time: 2:55 PM
 */

namespace XGallery\Application\Nct;

use Joomla\Filesystem\Folder;
use XGallery\Application\Nct;
use XGallery\Environment\Filesystem\Helper;
use XGallery\Factory;

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
	 * @return boolean|void
	 *
	 * @since  2.0.0
	 * @throws \Exception
	 */
	public function execute()
	{
		$id    = $this->input->getInt('id', null);
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__nct_songs'));

		if ($id !== null)
		{
			$query->where($db->quoteName('id') . ' = ' . (int) $id);
		}

		$query->where($db->quoteName('state') . ' = 0');

		$songs = $db->setQuery($query)->loadObjectList();

		foreach ($songs as $index => $song)
		{
			$this->download($song);
		}
	}

	/**
	 * @param   object $song Song object
	 *
	 * @return  boolean
	 * @throws  \Exception
	 *
	 * @since   2.1.0
	 */
	private function download($song)
	{
		if ($song)
		{
			$db = Factory::getDbo();

			$songData     = $this->service->getData($song->play_url);
			$downloadLink = trim($this->service->getDownloadLink($songData['flashlink']));

			$fileName = explode('?', basename($downloadLink));
			$fileName = $fileName[0];

			$toDir = Factory::getConfiguration()->get('media_dir', XPATH_ROOT . '/NCT/' . $songData['singer']);

			if (!is_dir($toDir))
			{
				Folder::create($toDir);
			}

			$saveTo = $toDir . '/' . $fileName;

			Helper::downloadFile($downloadLink, $saveTo);

			$song->singer    = $songData['singer'];
			$song->flash_url = $songData['flashlink'];
			$song->state     = 1;

			return $db->updateObject('#__nct_songs', $song, 'id');
		}

		return false;
	}
}
