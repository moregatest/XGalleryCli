<?php
/**
 * @package     XGallery.Cli
 * @subpackage  Entrypoint
 *
 * @copyright   Copyright (C) 2012 - 2018 JOOservices.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use XGallery\Factory;

require_once __DIR__ . '/bootstrap.php';

$application = Factory::getApplication(Factory::getInput()->getCmd('application', Factory::getConfiguration()->get('application')));

if ($application)
{
	$application->execute();
}
