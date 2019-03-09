<?php

require_once __DIR__.'/bootstrap.php';

use XGallery\Applications\Cli\ApplicationCliFlickr;

$cli = new ApplicationCliFlickr;
$cli->run();