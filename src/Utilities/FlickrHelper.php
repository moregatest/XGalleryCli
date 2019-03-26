<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Utilities;

use XGallery\Factory;

/**
 * Class FlickrHelper
 * @package XGallery\Utilities
 */
class FlickrHelper
{
    /**
     * Get by NSID by URL or ID
     *
     * @param string $id
     * @return boolean|string
     */
    public static function getNsid($id)
    {
        if (filter_var($id, FILTER_VALIDATE_URL)) {
            $user = Factory::getServices('flickr')->flickrUrlsLookupUser($id);

            if (!$user) {
                return false;
            }

            return $user->user->id;
        }

        return $id;
    }

    /**
     * Get all photos in Album via URL
     *
     * @param $albumUrl
     * @return array
     */
    public static function getAlbumPhotos($albumUrl)
    {
        $parts   = explode('/', $albumUrl);
        $nsid    = self::getNsid($albumUrl);
        $albumId = end($parts);

        return [
            'nsid' => $nsid,
            'album' => $albumId,
            'photos' => Factory::getServices('flickr')->flickrPhotoSetsGetAllPhotos($albumId, $nsid),
        ];
    }

    /**
     * @param string $nsid
     * @return mixed|array
     */
    public static function getAllFavorities($nsid)
    {
        return Factory::getServices('flickr')->flickrFavoritesGetAllList(self::getNsid($nsid));
    }
}
