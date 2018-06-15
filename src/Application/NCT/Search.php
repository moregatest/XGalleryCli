<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application\NCT;

use XGallery\Application\Nct;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Cli
 * @subpackage  Application.NCT
 *
 * @since       2.0.0
 */
class Search extends Nct
{
	/**
	 * @return boolean
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @since  2.1.0
	 */
	public function doExecute()
	{
		$filter = $this->input->get('filter');
		$pages  = $this->getPages($filter);

		for ($page = 1; $page <= $pages; $page++)
		{
			$this->getSongs('https://www.nhaccuatui.com/tim-nang-cao?' . $filter . '&page=' . $page);
		}

		return parent::doExecute();
	}
}
