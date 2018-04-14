<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Joomla\Database\DatabaseQuery;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Model.Base
 *
 * @since       2.0.0
 */
class Model
{
	/**
	 * @param   string $name Model name
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public static function getInstance($name)
	{
		static $instances;

		if (!isset($instances[$name]))
		{
			$class = '\\XGallery\Model\\' . ucfirst($name);

			if (!class_exists($class))
			{
				return false;
			}

			$instances[$name] = new $class;
		}

		return $instances[$name];
	}

	/**
	 *
	 * @return null|object
	 *
	 * @since  2.0.0
	 */
	public function getMaxConnection()
	{
		return $this->getDbo()->setQuery('show variables like \'max_connections\'')->loadObject();
	}

	/**
	 * @return \Joomla\Database\DatabaseDriver
	 *
	 * @since   2.0.02
	 */
	public function getDbo()
	{
		return Factory::getDbo();
	}

	/**
	 * @param   DatabaseQuery $query Query
	 *
	 * @return  boolean|mixed
	 *
	 * @since   2.0.0
	 *
	 * @throws \Exception
	 */
	protected function insertRows($query)
	{
		$query = (string) $query;
		$query = trim($query, ',');

		// Try to execute INSERT IGNORE
		try
		{
			$query = (string) $query;
			$query = str_replace('INSERT', 'INSERT IGNORE', $query);

			// Ignore duplicate
			return $this->getDbo()->setQuery($query)->execute();
		}
		catch (\Exception $exception)
		{
			Factory::getLogger()->error($exception->getMessage());

			return false;
		}
	}
}
