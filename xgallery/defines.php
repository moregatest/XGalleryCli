<?php

// No direct access.
defined('_XEXEC') or die;

define('XGALLERY_DEFAULT_SERVICE', 'Flickr');
define('XGALLERY_DEFAULT_APPLICATION', 'Contacts');

define('XPATH_CACHE', JPATH_ROOT . '/cache');

define('XPATH_LIBRARIES', XPATH_BASE . '/libraries');
define('XPATH_SRC', XPATH_BASE . DIRECTORY_SEPARATOR . 'src');

define('XPATH_LOG', JPATH_ROOT . '/logs/xgallery/');
define('XPATH_MEDIA', JPATH_ROOT . '/media/xgallery/');

// Flickr
define('XGALLERY_FLICKR_SAFE_SEARCH', 3);
define('XGALLERY_FLICKR_PEOPLE_GETPHOTOS_PERPAGE', 500);
define('XGALLERY_FLICKR_FAVORITES_GETLIST_PERPAGE', 500);
define('XGALLERY_FLICKR_CONTACTS_GETLIST_PERPAGE', 1000);
define('XGALLERY_FLICKR_DOWNLOAD_PHOTOS_LIMIT', 250);

define('XGALLERY_FLICKR_PHOTO_STATE_PENDING', 0);
define('XGALLERY_FLICKR_PHOTO_STATE_SIZED', 1);
define('XGALLERY_FLICKR_PHOTO_STATE_DOWNLOADED', 2);
