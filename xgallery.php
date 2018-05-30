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

$input = Factory::getInput()->cli;

$application = Factory::getApplication($input->getCmd('application', XGALLERY_DEFAULT_APPLICATION));
$task        = $input->getCmd('task', 'execute');

if ($application && method_exists($application, $task))
{
	call_user_func(array($application, $task));
}
