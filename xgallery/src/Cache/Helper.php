<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Cache.Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Cache;


// No direct access.
defined('_XEXEC') or die;


/**
 * @package     XGallery.Cli
 * @subpackage  Cache.Helper
 *
 * @since       2.0.0
 */
class Helper
{
	/**
	 *
	 * @return \Stash\Pool
	 *
	 * @since   2.0.0
	 */
	public static function getPool()
	{
		static $pool;

		if (isset($pool))
		{
			return $pool;
		}

		$driver = new \Stash\Driver\FileSystem(array('path' => JPATH_ROOT . '/cache'));

		// Inject the driver into a new Pool object.
		$pool = new \Stash\Pool($driver);

		return $pool;
	}

	/**
	 * @param   string $key Key
	 *
	 * @return \Stash\Interfaces\ItemInterface
	 *
	 * @since   2.0.0
	 */
	public static function getItem($key)
	{
		return self::getPool()->getItem($key);
	}

	/**
	 * @param   \Stash\Item $item Item
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public static function save($item)
	{
		return self::getPool()->save($item);
	}
}
