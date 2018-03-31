<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Application.Flickr
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace XGallery\Application;

use XGallery\Factory;
use XGallery\Model;

/**
 * Class Flickr
 * @package     XGallery\Application
 *
 * @since       2.0.0
 */
class Flickr extends Cli
{
	/**
	 * @param   string $name Model name
	 *
	 * @return Model\Flickr|mixed
	 */
	protected function getModel($name = 'Flickr')
	{
		return Model::getInstance($name);
	}

	/**
	 * @return  boolean|mixed
	 *
	 * @since   2.0.02
	 */
	protected function install()
	{
		if (!is_file(XPATH_CONFIGURATION_FILE) || !file_exists(XPATH_CONFIGURATION_FILE))
		{
			$query = file_get_contents(XPATH_ROOT . '/install.sql');

			return Factory::getDbo()->setQuery($query)->execute();
		}

		return true;
	}
}
