<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
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
}
