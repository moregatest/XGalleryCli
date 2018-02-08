<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Libraries.Model
 *
 * @since       2.0.0
 */
class XgalleryModelFlickr extends XgalleryModelBase
{
	/**
	 * @param   integer $limit Limit
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function getContact($limit = 1)
	{
		$db = \Joomla\CMS\Factory::getDbo();

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
	 */
	public function updateContact($nsid, $data = array())
	{
		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		$data['updated'] = \Joomla\CMS\Date\Date::getInstance()->toSql();

		$query->update($db->quoteName('#__xgallery_flickr_contacts'));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$query->where($db->quoteName('nsid') . ' = ' . $db->quote($nsid));

		return $db->setQuery($query)->execute();
	}

	/**
	 * @param $pid
	 *
	 * @return mixed
	 *
	 * @since  2.0.0
	 */
	public function getPhoto($pid)
	{
		$db = \Joomla\CMS\Factory::getDbo();

		$query = ' SELECT ' . $db->quoteName('urls') . ',' . $db->quoteName('owner')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid)
			. ' LIMIT 1 FOR UPDATE ';

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
	public function getPhotos($nsid, $limit, $offset, $state = 0)
	{
		$db = \Joomla\CMS\Factory::getDbo();

		// Get photo sizes of current contact
		$query = 'SELECT ' . $db->quoteName('id')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('state') . ' = ' . (int) $state
			. ' AND ' . $db->quoteName('owner') . ' = ' . $db->quote($nsid)
			. ' LIMIT ' . (int) $limit . ' OFFSET ' . $offset . ' FOR UPDATE;';

		return $db->setQuery($query)->loadColumn();
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
		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__xgallery_flickr_contact_photos'));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$query->where($db->quoteName('id') . ' = ' . $db->quote($pid));

		return $db->setQuery($query)->execute();
	}

	/**
	 *
	 * @return boolean|mixed
	 *
	 * @since  2.0.0
	 */
	public function insertContactsFromFlickr()
	{
		XgalleryHelperLog::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		$contacts = \XGallery\Flickr\Flickr::getInstance()->getContactsList();

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
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage());

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
			XgalleryHelperLog::getLogger()->warning('No nsid provided');

			return false;
		}

		// Fetch photos
		$photos = \XGallery\Flickr\Flickr::getInstance()->getPhotosList($nsid);

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

			$values[] = isset($photo->id) ? $db->quote($photo->id) : $db->quote('');
			$values[] = isset($photo->owner) ? $db->quote($photo->owner) : $db->quote('');
			$values[] = isset($photo->secret) ? $db->quote($photo->secret) : $db->quote('');
			$values[] = isset($photo->server) ? $db->quote($photo->server) : $db->quote('');
			$values[] = isset($photo->farm) ? $db->quote($photo->farm) : $db->quote('');
			$values[] = isset($photo->title) ? $db->quote($photo->title) : $db->quote('');
			$values[] = isset($photo->ispublic) ? $db->quote($photo->ispublic) : $db->quote('');
			$values[] = isset($photo->isfriend) ? $db->quote($photo->isfriend) : $db->quote('');
			$values[] = isset($photo->isfamily) ? $db->quote($photo->isfamily) : $db->quote('');
			$values[] = isset($photo->urls) ? $db->quote($photo->urls) : $db->quote('');
			$values[] = isset($photo->state) ? $db->quote($photo->state) : 0;

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
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage());

			return false;
		}

		// Only process if this user have any photos
		try
		{
			$this->downloadPhotos($nsid, XGALLERY_FLICKR_DOWNLOAD_PHOTOS_LIMIT, 0);
		}
		catch (Exception $exception)
		{
			XgalleryHelperLog::getLogger()->error($exception->getMessage(), array('query' => (string) $query));
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
			$this->updatePhoto($pid, array('urls' => json_encode($sized), 'state' => 1));

			XgalleryHelperEnvironment::exec(XPATH_CLI_FLICKR . '/download.php --pid=' . $pid);
		}

		return true;
	}
}
