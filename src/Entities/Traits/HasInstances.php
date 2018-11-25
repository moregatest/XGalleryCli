<?php

namespace XGallery\Entities\Traits;

/**
 * Trait HasInstances
 * @package XGallery\Entities\Traits
 */
trait HasInstances
{
	/**
	 * Cached instances
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Remove an instance from cache.
	 *
	 * @param   integer $id Class identifier
	 *
	 * @return  void
	 */
	public static function clear($id)
	{
		unset(static::$instances[get_called_class()][$id]);
	}

	/**
	 * Clear all instances from cache
	 *
	 * @return  void
	 */
	public static function clearAll()
	{
		unset(static::$instances[get_called_class()]);
	}

	/**
	 * Ensure that we retrieve a non-statically-cached instance.
	 *
	 * @param   integer $id Identifier of the instance
	 *
	 * @return  $this
	 */
	public static function fresh($id)
	{
		static::clear($id);

		return static::find($id);
	}

	/**
	 * Create and return a cached instance
	 *
	 * @param   integer $id Identifier of the instance
	 *
	 * @return  $this
	 */
	public static function find($id)
	{
		$class = get_called_class();

		if (empty(static::$instances[$class][$id]))
		{
			static::$instances[$class][$id] = new static($id);
		}

		return static::$instances[$class][$id];
	}
}
