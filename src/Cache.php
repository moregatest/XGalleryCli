<?php
/**
 * @package     XGalleryCli
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

use Stash\Interfaces\ItemInterface;
use Stash\Pool;

defined('_XEXEC') or die;

/**
 * Class Cache
 * @package XGallery
 *
 * @since   2.1.0
 */
class Cache extends Pool
{
	/**
	 * @param   ItemInterface $item     Item
	 * @param   integer       $interval Interval time
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
