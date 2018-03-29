<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Base
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery;

defined('_XEXEC') or die;

/**
 * @package     XGallery.Application
 * @subpackage  Base
 *
 * @since       2.0.0
 */
class Application
{
	/**
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function execute()
	{
		return true;
	}

	public function install()
	{
		if (!is_file(XPATH_CONFIGURATION_FILE) || !file_exists(XPATH_CONFIGURATION_FILE))
		{
			$query = file_get_contents(XPATH_ROOT . '/install.sql');

			return Factory::getDbo()->setQuery($query)->execute();
		}

		return true;
	}
}
