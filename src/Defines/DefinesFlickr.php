<?php

namespace XGallery\Defines;

/**
 * Class DefinesFlickr
 * @package XGallery\Defines
 */
class DefinesFlickr
{
    /**
     * Limit number of requests to get photo sizes
     */
    const REST_LIMIT_PHOTOS_SIZE = 200;
    const DOWNLOAD_LIMIT = 100;
    const PHOTO_STATUS_DOWNLOADED = 1;
    const PHOTO_STATUS_ALREADY_DOWNLOADED = 2;
    const PHOTO_STATUS_FORCE_REDOWNLOAD = 3;
    const PHOTO_STATUS_SKIP_DOWNLOAD = 4;
    const PHOTO_STATUS_REDOWNLOAD_CORRUPTED = 5;
    const PHOTO_STATUS_ERROR_NOT_FOUND = -1;
    const PHOTO_STATUS_ERROR_NOT_PHOTO = -2;
    const PHOTO_STATUS_ERROR_DOWNLOAD_FAILED = -3;
    const PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED = -4;
}