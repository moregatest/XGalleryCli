<?php

class XgalleryModelFlickr extends XgalleryModelBase
{
	public function insertContacts()
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
	}
}
