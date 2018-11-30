<?php
/**
 * @package     XGalleryCli.Entity
 * @subpackage  Flickr Interface
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Entities;

defined('_XEXEC') or die;

/**
 * Interface EntityInterface
 * @package XGallery\Entities
 */
interface EntityInterface
{
	/**
	 * Get the attached database row.
	 *
	 * @return  array
	 */
	public function all();

	/**
	 * Assign a value to entity property.
	 *
	 * @param   string $property Name of the property to set
	 * @param   mixed  $value    Value to assign
	 *
	 * @return  self
	 */
	public function assign($property, $value);

	/**
	 * Get a property of this entity.
	 *
	 * @param   string $property Name of the property to get
	 * @param   mixed  $default  Value to use as default if property is not set or is null
	 *
	 * @return  mixed
	 */
	public function get($property, $default = null);

	/**
	 * @param   string $property Name of the property to get
	 * @param   mixed  $value    Value
	 *
	 * @return  mixed
	 */
	public function set($property, $value);

	/**
	 * Check if this entity has an identifier.
	 *
	 * @return  boolean
	 */
	public function hasId();

	/**
	 * Get the entity identifier.
	 *
	 * @return  integer
	 */
	public function id();

	/**
	 * Get this entity name.
	 *
	 * @return  string
	 */
	public function name();

	/**
	 * Get entity primary key column.
	 *
	 * @return  string
	 */
	public function primaryKey();
}
