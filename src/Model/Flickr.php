<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Model;

use XGallery\Environment\Helper;
use XGallery\Factory;
use XGallery\System\Configuration;

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
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 *
	 * @throws \Exception
	 */
	public function insertContactsFromFlickr()
	{
		Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$config = Configuration::getInstance();

		$lastExecutedTime = (int) $config->getConfig('flickr_contacts_last_executed');

		// No need update contact if cache is not expired
		if ($lastExecutedTime && time() - $lastExecutedTime < 3600)
		{
			Factory::getLogger()->notice('Cache is not expired. No need update contacts');

			return true;
		}

		$contacts          = Factory::getService('Flickr')->getContactsList();
		$totalContacts     = count($contacts);
		$lastTotalContacts = $config->getConfig('flickr_contacts_count');

		Factory::getLogger()->info('Contacts: ' . $totalContacts);

		if ($lastTotalContacts && $lastTotalContacts == $totalContacts)
		{
			Factory::getLogger()->notice('Have no new contacts');

			return true;
		}

		$config->setConfig('flickr_contacts_count', $totalContacts);
		$config->save();

		if (empty($contacts))
		{
			Factory::getLogger()->notice('Have no contacts');

			return false;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
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
					'location'
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

		$return = false;

		// Try to execute INSERT IGNORE
		try
		{
			$query = (string) $query;
			$query = str_replace('INSERT', 'INSERT IGNORE', $query);

			// Ignore duplicate
			$return = $db->setQuery($query)->execute();
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage());
		}

		return $return;
	}

	/**
	 * @param   string $nsid Nsid
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function insertPhotosFromFlickr($nsid)
	{
		// No nsid provided
		if (!$nsid || empty($nsid))
		{
			Factory::getLogger()->warning('No nsid provided');

			return false;
		}

		$db = $this->getDbo();

		// Transaction: Get a contact then fetch all photos of this contact
		try
		{
			$db->transactionStart();

			$this->updateContact($nsid);

			$db->transactionCommit();
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage(), array('query' => (string) $db->getQuery()));
			$db->transactionRollback();
		}

		// Fetch photos
		$photos = Factory::getService('Flickr')->getPhotosList($nsid);
		Factory::getLogger()->info('Photos: ' . count($photos));

		if (empty($photos))
		{
			return false;
		}

		// Got photos now insert to database
		$db    = $this->getDbo();
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
					'state'
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
			Factory::getLogger()->error($exception->getMessage());

			return false;
		}

		$config = Configuration::getInstance();

		// Only process if this user have any photos
		try
		{
			if (!$config->getConfig('flickr_download_limit'))
			{
				$maxConnection = $this->getMaxConnection();
				$config->setConfig('flickr_download_limit', $maxConnection->Value);
				$config->save();
			}

			$limit = $config->getConfig('flickr_download_limit', $config->setConfig('flickr_download_limit', 100));
			$this->downloadPhotos($nsid, $limit, 0);
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage(), array('query' => (string) $query));

			return false;
		}

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
	 * @throws  \Exception
	 */
	public function downloadPhotos($nsid, $limit, $offset)
	{
		// Get photo sizes of current contact
		$photos = $this->getPhotos($nsid, $limit, $offset);

		if (!$photos || empty($photos))
		{
			return false;
		}

		foreach ($photos as $photo)
		{
			$sized = Factory::getService('Flickr')->getPhotoSizes($photo->id);

			if (!$sized)
			{
				continue;
			}

			$sized = json_encode($sized);

			// Update sized
			$this->updatePhoto($photo->id, array('urls' => $sized, 'state' => XGALLERY_FLICKR_PHOTO_STATE_SIZED));

			$input               = Factory::getInput()->cli;
			$args                = $input->getArray();
			$args['application'] = 'Flickr.Download';
			$args['pid']         = $photo->id;

			$photo->urls  = $sized;
			$photo->state = XGALLERY_FLICKR_PHOTO_STATE_SIZED;
			$item         = \XGallery\Cache\Helper::getItem('flickr/photo/' . $photo->id);
			$item->set($photo);

			\XGallery\Cache\Helper::save($item);

			Helper::execService($args);
		}

		return true;
	}
}
