<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model.Nct
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Model;

use XGallery\Factory;
use XGallery\Model;

/**
 * Class Nct
 * @package XGallery\Model
 *
 * @since   2.1.0
 */
class Nct extends Model
{
	/**
	 * @param   integer $id Id
	 *
	 * @return  mixed
	 */
	public function getSongs($id = null)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__nct_songs'));

		if ($id)
		{
			$query->where($db->quoteName('id') . ' = ' . (int) $id);
		}
		else
		{
			$query->where($db->quoteName('state') . ' = 0');
		}

		return $db->setQuery($query, 0, 100)->loadObjectList();
	}
}
