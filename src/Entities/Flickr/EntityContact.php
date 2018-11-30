<?php

namespace XGallery\Entities\Flickr;


use XGallery\Entities\Entity;
use XGallery\Webservices\Services\Flickr;

/**
 * Class EntityContact
 * @package XGallery\Entities\Flickr
 */
class EntityContact extends Entity
{
	/**
	 * @return $this|EntityContact
	 * @throws \Exception
	 */
	public function fetch()
	{
		$contact = Flickr::getInstance()->getInfo($this->id());
		$this->bind($contact->person);

		return $this;
	}
}
