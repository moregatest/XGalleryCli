<?php

// No direct access.
defined('_XEXEC') or die;

class XgalleryModelFlickr extends XgalleryModelBase
{
	public function getContact()
	{
		$db = \Joomla\CMS\Factory::getDbo();
		// Fetch photos of a contact
		$rawQuery = ' SELECT ' . $db->quoteName('nsid')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contacts')
			. ' ORDER BY ' . $db->quoteName('updated') . ' ASC'
			. ' LIMIT 1 FOR UPDATE;';

		return $db->setQuery($rawQuery)->loadResult();
	}

	public function updateContact($nsid)
	{
		$db       = \Joomla\CMS\Factory::getDbo();
		$rawQuery = ' UPDATE ' . $db->quoteName('#__xgallery_flickr_contacts')
			. ' SET ' . $db->quoteName('updated') . ' = ' . $db->quote(\Joomla\CMS\Date\Date::getInstance()->toSql())
			. ' WHERE ' . $db->quoteName('nsid') . ' = ' . $db->quote($nsid);

		return $db->setQuery($rawQuery)->execute();
	}

	/**
	 *
	 * @return bool|mixed
	 *
	 * @since  2.0.0
	 */
	public function insertContactsFromFlickr()
	{
		XgalleryHelperLog::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		$contacts = XgalleryFlickr::getInstance()->getContactsList();

		// Insert contacts
		if (!empty($contacts))
		{
			$query->insert($db->quoteName('#__xgallery_flickr_contacts'));
			$query->columns($db->quoteName(array(
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
			)));

			foreach ($contacts as $contact)
			{
				$values   = array();
				$values[] = isset($contact->nsid) ? $db->quote($contact->nsid) : $db->quote('');
				$values[] = isset($contact->username) ? $db->quote($contact->username) : $db->quote('');
				$values[] = isset($contact->iconserver) ? $db->quote($contact->iconserver) : $db->quote('');
				$values[] = isset($contact->iconfarm) ? $db->quote($contact->iconfarm) : $db->quote('');
				$values[] = isset($contact->ignored) ? $db->quote($contact->ignored) : $db->quote('');
				$values[] = isset($contact->rev_ignored) ? $db->quote($contact->rev_ignored) : $db->quote('');
				$values[] = isset($contact->realname) ? $db->quote($contact->realname) : $db->quote('');
				$values[] = isset($contact->friend) ? $db->quote($contact->friend) : $db->quote('');
				$values[] = isset($contact->family) ? $db->quote($contact->family) : $db->quote('');
				$values[] = isset($contact->path_alias) ? $db->quote($contact->path_alias) : $db->quote('');
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

		return false;
	}

	public function downloadPhotos($nsid, $limit, $offset)
	{
		// Get photo sizes of current contact
		$pids = $this->getPhotos($nsid, $limit, $offset);

		foreach ($pids as $pid)
		{
			$sized = XgalleryFlickr::getInstance()->getPhotoSizes($pid);

			if (!$sized)
			{
				continue;
			}

			if ($sized->stat != "ok")
			{
				continue;
			}

			// Update sized
			$this->updatePhotoSizes($pid, $sized);

			XgalleryHelperEnv::exec(XPATH_CLI_FLICKR . '/download.php --pid=' . $pid);
		}
	}

	public function getPhotos($nsid, $limit, $offset)
	{
		$db = \Joomla\CMS\Factory::getDbo();
		// Get photo sizes of current contact
		$query = 'SELECT ' . $db->quoteName('id')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('state') . ' = 0 '
			. ' AND ' . $db->quoteName('owner') . ' = ' . $db->quote($nsid)
			. ' LIMIT ' . (int) $limit . ' OFFSET ' . $offset . ' FOR UPDATE;';

		return $db->setQuery($query)->loadColumn();
	}

	public function updatePhotoSizes($pid, $sized)
	{
		$db    = \Joomla\CMS\Factory::getDbo();
		$query = ' UPDATE ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' SET '
			. $db->quoteName('urls') . ' = ' . $db->quote(json_encode($sized))
			. ',' . $db->quoteName('state') . ' = 1'
			. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid);

		return $db->setQuery($query)->execute();
	}

	public function updatePhotoState($pid, $state)
	{
		$db    = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);
		// Update this photo status
		$query->clear()
			->update($db->quoteName('#__xgallery_flickr_contact_photos'))
			->set(array(
				$db->quoteName('state') . ' = ' . (int) $state
			))
			->where($db->quoteName('id') . ' = ' . $db->quote($pid));

		return $db->setQuery($query)->execute();
	}

	/**
	 * @param $pid
	 *
	 * @return mixed
	 *
	 * @since  2.0.0
	 */
	public function getFlickrPhoto($pid)
	{
		$db = \Joomla\CMS\Factory::getDbo();

		$query = ' SELECT ' . $db->quoteName('urls') . ',' . $db->quoteName('owner')
			. ' FROM ' . $db->quoteName('#__xgallery_flickr_contact_photos')
			. ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($pid)
			. ' LIMIT 1 FOR UPDATE ';

		return $db->setQuery($query)->loadObject();
	}
}
