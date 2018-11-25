<?php

namespace XGallery\Entities\Traits;

use stdClass;

/**
 * Trait HasObject
 * @package XGallery\Entities\Traits
 */
trait HasObject
{
	/**
	 * @var \stdClass
	 */
	protected $item;

	/**
	 * @param   mixed $properties   Either and associative array or another
	 *                              object to set the initial properties of the object.
	 */
	protected function initObject($properties = null)
	{
		$this->item = new stdClass;

		if ($properties !== null)
		{
			$this->setProperties($properties);
		}
	}

	/**
	 * Magic method to convert the object to a string gracefully.
	 *
	 * @return  string  The classname.
	 *
	 */
	public function __toString()
	{
		return get_class($this);
	}

	/**
	 * Sets a default value if not already assigned
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $default  The default value.
	 *
	 * @return  mixed
	 */
	public function defProperty($property, $default = null)
	{
		$value = $this->getProperty($property, $default);

		return $this->setProperty($property, $value);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $default  The default value.
	 *
	 * @return  mixed    The value of the property.
	 */
	public function getProperty($property, $default = null)
	{
		if (isset($this->item->{$property}))
		{
			return $this->item->{$property};
		}

		return $default;
	}

	/**
	 * Returns an associative array of object properties.
	 *
	 * @param   boolean $public If true, returns only the public properties.
	 *
	 * @return  array
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this->item);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $value    The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 */
	public function setProperty($property, $value = null)
	{
		$previous                = isset($this->item->{$property}) ? $this->item->{$property} : null;
		$this->item->{$property} = $value;

		return $previous;
	}

	/**
	 * Set the object properties based on a named array/hash.
	 *
	 * @param   mixed $properties Either an associative array or another object.
	 *
	 * @return  boolean
	 */
	public function setProperties($properties)
	{
		if (is_array($properties) || is_object($properties))
		{
			foreach ((array) $properties as $k => $v)
			{
				// Use the set function which might be overridden.
				$this->setProperty($k, $v);
			}

			return true;
		}

		return false;
	}

	/**
	 * @param $property
	 *
	 * @return boolean
	 */
	public function isExist($property)
	{
		return isset($this->item->{$property});
	}

}
