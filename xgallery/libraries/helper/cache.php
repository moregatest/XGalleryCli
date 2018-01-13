<?php

// No direct access.
defined('_XEXEC') or die;

/**
 * @package     ${NAMESPACE}
 *
 * @since       2.0.0
 */
class XgalleryHelperCache
{
	public static function getPool()
	{
		static $pool;

		if (isset($pool))
		{
			return $pool;
		}

		$driver = new Stash\Driver\FileSystem(array('path' => JPATH_ROOT . '/cache'));

		// Inject the driver into a new Pool object.
		$pool = new Stash\Pool($driver);

		return $pool;
	}

	public static function getItem($key)
	{
		return self::getPool()->getItem($key);
	}

	public static function save($item)
	{
		return self::getPool()->save($item);
	}
}