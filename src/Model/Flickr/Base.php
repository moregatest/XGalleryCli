<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Model\Flickr;

use Joomla\CMS\Factory;
use XGallery\Log\Helper;
use XGallery\Model;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @since       2.0.0
 */
class Base extends Model
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
		\XGallery\Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

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
	 */
	public function updateContact($nsid, $data = array())
	{
		\XGallery\Factory::getLogger()->info(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__xgallery_flickr_contacts'));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$query->set($db->quoteName('updated') . ' = now()');

		$query->where($db->quoteName('nsid') . ' = ' . $db->quote($nsid));

		return $db->setQuery($query)->execute();
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
