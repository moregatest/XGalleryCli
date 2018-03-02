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

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Flickr
 *
 * @since       2.0.0
 */
class Base extends \XGallery\Model\Base
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
		Helper::getLogger()->info(__CLASS__ . '.' . __FUNCTION__);

		$db = Factory::getDbo();

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
		Helper::getLogger()->info(__CLASS__ . '.' . __FUNCTION__, func_get_args());

		$db    = Factory::getDbo();
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
	 * @param   string $pid Pid
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function getPhoto($pid)
	{
		$db = Factory::getDbo();

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
		$db = Factory::getDbo();

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

		$db    = Factory::getDbo();
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
