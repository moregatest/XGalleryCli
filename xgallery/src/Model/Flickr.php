<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Model;

use Joomla\CMS\Factory;
use XGallery\Environment\Helper;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @since       2.0.0
 */
class Flickr extends Flickr\Base
{
	/**
	 *
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 */
	public function insertContactsFromFlickr()
	{
		\XGallery\Log\Helper::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$contacts = \XGallery\Flickr\Flickr::getInstance()->getContactsList();
		\XGallery\Log\Helper::getLogger()->info('Contacts: ' . count($contacts));

		if (empty($contacts))
		{
			return false;
		}

		$query->insert($db->quoteName('#__xgallery_flickr_contacts'));
		$query->columns(
			$db->quoteName(
				array(
					'nsid',
					'username',
					'iconserver',
					'iconfarm',
					'ignored',
					'rev_ignored',
					'realname',
					'friend',
					'family',
					'path_alias',
					'location',
				)
			)
		);

		foreach ($contacts as $contact)
		{
			$values = array();

			// Nsid
			$values[] = isset($contact->nsid) ? $db->quote($contact->nsid) : $db->quote('');

			// Username
			$values[] = isset($contact->username) ? $db->quote($contact->username) : $db->quote('');

			// Iconserver
			$values[] = isset($contact->iconserver) ? $db->quote($contact->iconserver) : $db->quote('');

			// Iconfarm
			$values[] = isset($contact->iconfarm) ? $db->quote($contact->iconfarm) : $db->quote('');

			// Ignored
			$values[] = isset($contact->ignored) ? $db->quote($contact->ignored) : $db->quote('');

			// Rev_ignored
			$values[] = isset($contact->rev_ignored) ? $db->quote($contact->rev_ignored) : $db->quote('');

			// Realname
			$values[] = isset($contact->realname) ? $db->quote($contact->realname) : $db->quote('');

			// Friend
			$values[] = isset($contact->friend) ? $db->quote($contact->friend) : $db->quote('');

			// Family
			$values[] = isset($contact->family) ? $db->quote($contact->family) : $db->quote('');

			// Path_alias
			$values[] = isset($contact->path_alias) ? $db->quote($contact->path_alias) : $db->quote('');

			// Location
			$values[] = isset($contact->location) ? $db->quote($contact->location) : $db->quote('');

			$query->values(implode(',', $values));
		}

		// Try to execute INSERT IGNORE
		try
		{
			$query = (string) $query;
			$query = str_replace('INSERT', 'INSERT IGNORE', $query);

			// Ignore duplicate
			return $db->setQuery($query)->execute();
		}
		catch (\Exception $exception)
		{
			\XGallery\Log\Helper::getLogger()->error($exception->getMessage());

			return false;
		}
	}

	/**
	 * @param   string $nsid Nsid
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function insertPhotosFromFlickr($nsid)
	{
		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			\XGallery\Log\Helper::getLogger()->warning('No nsid provided');

			return false;
		}

		// Fetch photos
		$photos = \XGallery\Flickr\Flickr::getInstance()->getPhotosList($nsid);
		\XGallery\Log\Helper::getLogger()->info('Photos: ' . count($photos));

		if (empty($photos))
		{
			return false;
		}

		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__xgallery_flickr_contact_photos'));
		$query->columns(
			$db->quoteName(
				array(
					'id',
					'owner',
					'secret',
					'server',
					'farm',
					'title',
					'ispublic',
					'isfriend',
					'isfamily',
					'urls',
					'state',
				)
			)
		);

		foreach ($photos as $photo)
		{
			$values = array();

			// Id
			$values[] = isset($photo->id) ? $db->quote($photo->id) : $db->quote('');

			// Owner
			$values[] = isset($photo->owner) ? $db->quote($photo->owner) : $db->quote('');

			// Secret
			$values[] = isset($photo->secret) ? $db->quote($photo->secret) : $db->quote('');

			// Server
			$values[] = isset($photo->server) ? $db->quote($photo->server) : $db->quote('');

			// Farm
			$values[] = isset($photo->farm) ? $db->quote($photo->farm) : $db->quote('');

			// Title
			$values[] = isset($photo->title) ? $db->quote($photo->title) : $db->quote('');

			// Is public
			$values[] = isset($photo->ispublic) ? $db->quote($photo->ispublic) : $db->quote('');

			// Is friend
			$values[] = isset($photo->isfriend) ? $db->quote($photo->isfriend) : $db->quote('');

			// Is family
			$values[] = isset($photo->isfamily) ? $db->quote($photo->isfamily) : $db->quote('');

			// Urls
			$values[] = isset($photo->urls) ? $db->quote($photo->urls) : $db->quote('');

			// State
			$values[] = isset($photo->state) ? $db->quote($photo->state) : XGALLERY_FLICKR_PHOTO_STATE_PENDING;

			$query->values(implode(',', $values));
		}

		$query = trim($query, ',');

		// Try to execute INSERT IGNORE
		try
		{
			$query = (string) $query;
			$query = str_replace('INSERT', 'INSERT IGNORE', $query);

			// Ignore duplicate
			$db->setQuery($query)->execute();
		}
		catch (\Exception $exception)
		{
			\XGallery\Log\Helper::getLogger()->error($exception->getMessage());

			return false;
		}

		// Only process if this user have any photos
		try
		{
			$this->downloadPhotos($nsid, XGALLERY_FLICKR_DOWNLOAD_PHOTOS_LIMIT, 0);
		}
		catch (\Exception $exception)
		{
			\XGallery\Log\Helper::getLogger()->error($exception->getMessage(), array('query' => (string) $query));
		}

		$db->disconnect();

		return true;
	}

	/**
	 * @param   string  $nsid   Nsid
	 * @param   integer $limit  Limit
	 * @param   integer $offset Offset
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function downloadPhotos($nsid, $limit, $offset)
	{
		// Get photo sizes of current contact
		$pids = $this->getPhotos($nsid, $limit, $offset);

		if (!$pids || empty($pids))
		{
			return false;
		}

		foreach ($pids as $pid)
		{
			$sized = \XGallery\Flickr\Flickr::getInstance()->getPhotoSizes($pid);

			if (!$sized)
			{
				continue;
			}

			if ($sized->stat != "ok")
			{
				continue;
			}

			// Update sized
			$this->updatePhoto($pid, array('urls' => json_encode($sized), 'state' => XGALLERY_FLICKR_PHOTO_STATE_SIZED));

			Helper::exec(XPATH_CLI_FLICKR . '/download.php --pid=' . $pid);
		}

		return true;
	}
}
