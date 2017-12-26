<?php

class XgalleryModelBase
{
	public static function getInstance()
	{
		static $instace;

		if (!isset($instace))
		{
			$instace = new static;
		}

		return $instace;
	}
}