<?php

namespace XGallery\Entities;

use InvalidArgumentException;
use Joomla\Registry\Registry;
use stdClass;
use XGallery\Entities\Traits\HasInstances;
use XGallery\Entities\Traits\HasJson;
use XGallery\Entities\Traits\HasObject;

/**
 * Class Entity
 * @package XGallery\Entities
 */
abstract class Entity implements EntityInterface
{
	use HasObject;
	use HasJson;
	use HasInstances;

	/**
	 * Identifier.
	 *
	 * @var  integer
	 */
	protected $id;

	/**
	 * Constructor.
	 *
	 * @param   integer $id Identifier
	 */
	public function __construct($id = null)
	{
		$this->id = (int) $id;

		$this->initObject([$this->primaryKey() => $id]);
	}

	/**
	 * Proxy entity properties
	 *
	 * @param   string $property Property tried to access
	 *
	 * @return  mixed   Property if it exists
	 *
	 * @throws  \InvalidArgumentException  Column does not exist
	 */
	public function __get($property)
	{
		return $this->get($property);
	}

	/**
	 * Get all the entity properties.
	 *
	 * @return  array
	 */
	public function all()
	{
		if ($this->hasId() && !$this->isExist($this->primaryKey()))
		{
			$this->fetch();
		}

		return $this->getProperties();
	}

	/**
	 * Assign a value to entity property.
	 *
	 * @param   string $property Name of the property to set
	 * @param   mixed  $value    Value to assign
	 *
	 * @return  self
	 */
	public function assign($property, $value)
	{
		$this->setProperty($property, $value);

		if ($property === $this->primaryKey())
		{
			$this->id = (int) $value;
		}

		return $this;
	}

	/**
	 * Bind data to the entity.
	 *
	 * @param   mixed $data array | \stdClass Data to bind
	 *
	 * @return  self
	 */
	public function bind($data)
	{
		if (!is_array($data) && !$data instanceof stdClass)
		{
			throw new InvalidArgumentException(sprintf("Invalid data sent for %s::%s()", __CLASS__, __FUNCTION__));
		}

		$this->setProperties($data);
		$primaryValue = $this->getProperty($this->primaryKey());

		if ($primaryValue)
		{
			$this->id = (int) $primaryValue;
		}

		return $this;
	}

	/**
	 * Fetch DB data.
	 *
	 * @return  self
	 *
	 */
	abstract public function fetch();

	/**
	 * Fast method to create an instance from an array|object of data.
	 *
	 * @param   array|\stdClass $data Data to bind to the instance
	 *
	 * @return  static
	 */
	public static function fromData($data)
	{
		$entity = new static;

		return $entity->bind($data);
	}

	/**
	 * Get a property of this entity.
	 *
	 * @param   string $property Name of the property to get
	 * @param   mixed  $default  Value to use as default if property is null
	 *
	 * @return  mixed
	 *
	 * @throws  \InvalidArgumentException  Column does not exist
	 */
	public function get($property, $default = null)
	{
		return $this->getProperty($property, $default);
	}

	public function set($propery, $value)
	{
		return $this->setProperty($propery, $value);
	}

	/**
	 * Check if entity has a property.
	 *
	 * @param   string $property Entity property name
	 * @param   mixed  $callback Callable to execute for further verifications
	 *
	 * @return  boolean
	 */
	public function has($property, callable $callback = null)
	{
		if (!$this->isExist($property))
		{
			return false;
		}

		return $callback ? call_user_func($callback, $this->getProperty($property)) : true;
	}

	/**
	 * Check if a property exists and is empty.
	 *
	 * @param   string $property Entity property name
	 *
	 * @return  boolean
	 */
	public function hasEmpty($property)
	{
		return $this->has(
			$property,
			function ($value) {
				return empty($value);
			}
		);
	}

	/**
	 * Check if a property exists and is not empty.
	 *
	 * @param   string $property Entity property name
	 *
	 * @return  boolean
	 */
	public function hasNotEmpty($property)
	{
		return $this->has(
			$property,
			function ($value) {
				return !empty($value);
			}
		);
	}

	/**
	 * Check if this entity has an id.
	 *
	 * @return  boolean
	 */
	public function hasId()
	{
		return !empty($this->id);
	}

	/**
	 * Gets the Identifier.
	 *
	 * @return  integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Load an instance.
	 *
	 * @param   integer $id Instance identifier
	 *
	 * @return  static
	 */
	public static function load($id)
	{
		return static::find($id)->fetch();
	}

	/**
	 * Check if entity has been loaded.
	 *
	 * @return  boolean
	 */
	public function isLoaded()
	{
		return $this->hasId() & !empty($this->item);
	}

	/**
	 * Get this entity name.
	 *
	 * @return  string
	 */
	public function name()
	{
		$class = get_class($this);

		if (false !== strpos($class, '\\'))
		{
			$suffix = rtrim(strstr($class, 'Entity'), '\\');
			$parts  = explode("\\", $suffix);

			return $parts ? strtolower(end($parts)) : null;
		}

		$parts = explode('Entity', $class, 2);

		return $parts ? strtolower(end($parts)) : null;
	}

	/**
	 * Get entity primary key column.
	 *
	 * @return  string
	 */
	public function primaryKey()
	{
		return 'id';
	}

	/**
	 * Get a Registry object from a property of the entity.
	 *
	 * @param   string $property Property containing the data to import
	 *
	 * @return  Registry
	 *
	 * @throws  \InvalidArgumentException  Property does not exist
	 */
	public function registry($property)
	{
		return new Registry($this->get($property));
	}

	/**
	 * Unassigns a row property.
	 *
	 * @param   string $property Name of the property to set
	 *
	 * @return  self
	 */
	public function unassign($property)
	{
		if ($this->isExist($property))
		{
			unset($this->item->{$property});
		}

		return $this;
	}
}