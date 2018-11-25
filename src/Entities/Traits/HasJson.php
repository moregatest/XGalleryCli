<?php

namespace XGallery\Entities\Traits;

/**
 * Trait HasJson
 * @package XGallery\Entities\Traits
 */
trait HasJson
{
	abstract public function getProperties();

	public function toJson()
	{
		return json_encode($this->getProperties());
	}
}