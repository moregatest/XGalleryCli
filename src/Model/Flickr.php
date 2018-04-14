<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Model;

use XGallery\Factory;
use XGallery\Model;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @since       2.0.0
 */
class Flickr extends Model
{
	/**
	 * @param   array $contacts Contacts
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function insertContacts($contacts)
	{
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

		return $this->insertRows($query);
	}

	/**
	 * @param   integer $limit Limit
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function getContact($limit = 1)
	{
		$db = $this->getDbo();

		// Fetch photos of a contact
		$rawQuery = ' SELECT ' . $db->quoteName('nsid')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contacts')
			. ' ORDER BY ' . $db->quoteName('updated') . ' ASC'
			. ' LIMIT ' . (int) $limit . ' FOR UPDATE;';

		return $db->setQuery($rawQuery)->loadResult();
	}

	/**
	 * @param   string $nsid Nsid
	 * @param   array  $data Data
	 *
	 * @return mixed
	 *
	 * @since  2.0.0
	 *
	 * @throws \Exception
	 */
	public function updateContact($nsid, $data = array())
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__xgallery_flickr_contacts'));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$query->set($db->quoteName('updated') . ' = now()');

		$query->where($db->quoteName('nsid') . ' = ' . $db->quote($nsid));

		try
		{
			$db->transactionStart();

			if (!$db->setQuery($query)->execute())
			{
				Factory::getLogger()->error((string) $db->getQuery());

				return false;
			}

			$db->transactionCommit();
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage(), array('query' => (string) $db->getQuery()));
			$db->transactionRollback();
		}

		return true;
	}

	/**
	 * @param   array $photos Photos
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	public function insertPhotos($photos)
	{
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

		return $this->insertRows($query);
	}


	/**
	 * @param   string  $pid   Pid
	 * @param   integer $limit Limit
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function getPhoto($pid, $limit = 1)
	{
		$db = $this->getDbo();

		$query = ' SELECT ' . $db->quoteName('urls') . ',' . $db->quoteName('owner')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid)
			. ' LIMIT ' . $limit . ' FOR UPDATE ';

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * @param   string  $nsid   Nsid
	 * @param   integer $limit  Limit
	 * @param   integer $offset Offset
	 * @param   integer $state  State
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public function getPhotos($nsid, $limit, $offset, $state = XGALLERY_FLICKR_PHOTO_STATE_PENDING)
	{
		$db = $this->getDbo();

		// Get photo sizes of current contact
		$query = 'SELECT * '
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('state') . ' = ' . (int) $state
			. ' AND ' . $db->quoteName('owner') . ' = ' . $db->quote($nsid)
			. ' LIMIT ' . (int) $limit . ' OFFSET ' . $offset . ' FOR UPDATE;';

		return $db->setQuery($query)->loadObjectList();
	}

	/**
	 * @param   integer $pid  Photo id
	 * @param   array   $data Data
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function updatePhoto($pid, $data = array())
	{
		if (empty($data))
		{
			return false;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__xgallery_flickr_contact_photos'));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$query->where($db->quoteName('id') . ' = ' . $db->quote($pid));

		$return = $db->setQuery($query)->execute();

		return $return;
	}
}
