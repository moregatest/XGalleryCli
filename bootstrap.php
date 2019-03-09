<?php

require_once __DIR__.'/vendor/autoload.php';

define('XGALLERY_ROOT', __DIR__);

$dotenv = new \Symfony\Component\Dotenv\Dotenv;
$dotenv->load(__DIR__.'/config/.prod.env');