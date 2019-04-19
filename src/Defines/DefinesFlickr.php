<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Defines;

/**
 * Class DefinesFlickr
 * @package XGallery\Defines
 */
class DefinesFlickr
{
    /**
     * Default limit number of requests to get photo sizes
     */
    const REST_LIMIT_PHOTOS_SIZE = 150;
    /**
     * Default limit downloads / execute
     */
    const DOWNLOAD_LIMIT = 200;
    /**
     * Number photos to process resize
     */
    const RESIZE_LIMIT = 100;
    const PHOTO_STATUS_DOWNLOADED = 1;
    const PHOTO_STATUS_ALREADY_DOWNLOADED = 2;
    const PHOTO_STATUS_FORCE_REDOWNLOAD = 3;
    const PHOTO_STATUS_SKIP_DOWNLOAD = 4;
    const PHOTO_STATUS_REDOWNLOAD_CORRUPTED = 5;
    const PHOTO_STATUS_ERROR_NOT_FOUND = -1;
    const PHOTO_STATUS_ERROR_NOT_PHOTO = -2;
    const PHOTO_STATUS_ERROR_DOWNLOAD_FAILED = -3;
    const PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED = -4;
    const PHOTO_STATUS_ERROR_NOT_MATCH_REQUIREMENT = -5;
    const PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES = -10;

    const FLICKR_PHOTO_MIN_WIDTH = 800;
    const FLICKR_PHOTO_MIN_HEIGHT = 600;
}
