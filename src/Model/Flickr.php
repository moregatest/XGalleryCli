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
	 * @throws  \Exception
	 */
	public function insertContacts($contacts)
	{
		if (empty($contacts))
		{
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

		$fields = array(
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
		);

		var_dump($contacts);
		exit;

		foreach ($contacts as $contact)
		{
			$values = array();

			foreach ($fields as $field)
			{
				$values[] = isset($contact->{$field}) ? $db->quote($contact->{$field}) : $db->quote('');
			}

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

		$fields = array(
			'id',
			'owner',
			'secret',
			'server',
			'farm',
			'title',
			'ispublic',
			'isfriend',
			'isfamily',
			'urls'
		);

		foreach ($photos as $photo)
		{
			$values = array();

			foreach ($fields as $field)
			{
				$values[] = isset($photo->{$field}) ? $db->quote($photo->{$field}) : $db->quote('');
			}

			// State
			$values[] = isset($photo->state) ? $db->quote($photo->state) : XGALLERY_FLICKR_PHOTO_STATE_PENDING;

			$query->values(implode(',', $values));
		}

		return $this->insertRows($query);
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

		return $db->setQuery($query)->execute();
	}
}
