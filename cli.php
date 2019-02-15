<?php

require_once __DIR__ . '/bootstrap.php';

$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
$cli = new \XGallery\Applications\ApplicationFlickr;
$cli->setDispatcher($dispatcher);
$cli->run();