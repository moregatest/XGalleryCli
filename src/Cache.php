<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Factory
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Stash\Pool;

defined('_XEXEC') or die;

class Cache extends Pool
{
	/**
	 * @param   \Stash\Interfaces\ItemInterface $item     Item
	 * @param   integer                         $interval Interval time
	 *
	 * @return  boolean
	 *
	 * @since   2.1.0
	 */
	public function saveWithExpires($item, $interval = 3600)
	{
		$item->expiresAfter($interval);

		return $this->save($item);
	}
}