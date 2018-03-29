<?php

// No direct access.
defined('_XEXEC') or die;

define('XGALLERY_DEFAULT_APPLICATION', 'Flickr.Contacts');
define('JPATH_ROOT', __DIR__);
define('XPATH_ROOT', __DIR__);
define('XPATH_3RD', XPATH_ROOT . '/3rd');
define('XPATH_CACHE', XPATH_ROOT . '/cache');
define('XPATH_LOG', XPATH_ROOT . '/logs/xgallery/');
define('XPATH_MEDIA', XPATH_ROOT . '/media/');
define('XPATH_CONFIGURATION_FILE', XPATH_ROOT . '/config.json');

// Flickr
define('XGALLERY_FLICKR_SAFE_SEARCH', 3);
define('XGALLERY_FLICKR_PEOPLE_GETPHOTOS_PERPAGE', 500);
define('XGALLERY_FLICKR_FAVORITES_GETLIST_PERPAGE', 500);
define('XGALLERY_FLICKR_CONTACTS_GETLIST_PERPAGE', 1000);
define('XGALLERY_FLICKR_STAT_SUCCESS', 'ok');

define('XGALLERY_FLICKR_PHOTO_STATE_PENDING', 0);
define('XGALLERY_FLICKR_PHOTO_STATE_SIZED', 1);
define('XGALLERY_FLICKR_PHOTO_STATE_DOWNLOADED', 2);
