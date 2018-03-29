<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Cache.Helper
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Cache;

use Stash\Driver\FileSystem;
use Stash\Pool;

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

		$driver = new FileSystem(array('path' => XPATH_CACHE));

		// Inject the driver into a new Pool object.
		$pool = new Pool($driver);

		return $pool;
	}

	/**
	 * @param   string $key Key
	 *
	 * @return  \Stash\Interfaces\ItemInterface
	 *
	 * @since   2.0.0
	 */
	public static function getItem($key)
	{
		return self::getPool()->getItem($key);
	}

	/**
	 * @param   \Stash\Interfaces\ItemInterface $item     Item
	 * @param   integer                         $interval Interval time
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public static function save($item, $interval = 3600)
	{
		$item->expiresAfter($interval);

		return self::getPool()->save($item);
	}
}
