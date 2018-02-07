<?php

// No direct access.
defined('_XEXEC') or die;

define('XGALLERY_DEFAULT_SERVICE', 'flickr');
define('XGALLERY_DEFAULT_TASK', 'contacts');

define('XPATH_LIBRARIES', XPATH_BASE . '/libraries');
define('XPATH_CLI', XPATH_LIBRARIES . '/cli');
define('XPATH_CLI_FLICKR', XPATH_LIBRARIES . '/cli/flickr');

define('XPATH_LOG', JPATH_ROOT . '/logs/xgallery/');
define('XPATH_MEDIA', JPATH_ROOT . '/media/xgallery/');

// Flickr
define('XGALLERY_FLICKR_CONTACTS_PERPAGE', 1000);
define('XGALLERY_FLICKR_DOWNLOAD_PHOTOS_LIMIT', 250);
